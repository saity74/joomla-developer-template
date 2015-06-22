<?php defined('JPATH_PLATFORM') or die;

ini_set('display_errors', '0');

use Joomla\Registry\Registry;

jimport('joomla.utilities.utility');


class JDocumentHAML extends JDocument
{

    protected $haml;

    protected $hamlExecutor;

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);


        // Set document type
        $this->_type = 'haml';

        // Set default mime type and document metadata (meta data syncs with mime type by default)
        $this->setMimeEncoding('text/html');

        $this->haml = new MtHaml\Environment('php', array('enable_escaper' => false));

        $this->hamlExecutor = new MtHaml\Support\Php\Executor($this->haml, array('cache' => JPATH_CACHE.'/haml'));

    }

    function __call($method, $params) {

        if (substr($method,0,6) == 'render')
        {
            $name = (isset($params[0])) ? $params[0] : null;
            $attr = (isset($params[1])) ? $params[1] : null;

            return $this->getBuffer(strtolower(substr($method,6)), $name, $attr);
        }
        else
        {
            return NULL;
        }
    }

    public function parse($params = array())
    {
        return $this->_fetchTemplate($params);
    }

    protected function _fetchTemplate($params = array())
    {
        // Check
        $directory = isset($params['directory']) ? $params['directory'] : 'templates';
        $filter = JFilterInput::getInstance();
        $template = $filter->clean($params['template'], 'cmd');
        $file = $filter->clean($params['file'], 'cmd');

        if (!file_exists($directory . '/' . $template . '/' . $file))
        {
            $template = 'system';
        }

        // Load the language file for the template
        $lang = JFactory::getLanguage();

        // 1.5 or core then 1.6
        $lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
        || $lang->load('tpl_' . $template, $directory . '/' . $template, null, false, true);

        // Assign the variables
        $this->template = $template;
        $this->baseurl = JUri::base(true);
        $this->params = isset($params['params']) ? $params['params'] : new Registry;

        // Load
        $this->_template = $this->_loadTemplate($directory . '/' . $template, $file);

        return $this;
    }

    public function render($caching = false, $params = array())
    {

        $this->_caching = $caching;

        if (empty($this->_template))
        {
            $this->parse($params);
        }

        $data = $this->_renderTemplate();

        parent::render();

        return $data;
    }

    protected function _renderTemplate()
    {

        try {
            ob_start();
            $this->hamlExecutor->display($this->_file, ['tpl' => $this]);
            $contents = ob_get_contents();
            ob_end_clean();

        } catch (MtHaml\Exception $e) {
            echo "Failed to execute template: ", $e->getMessage(), "\n";
        }

        return $contents;

    }

    protected function _loadTemplate($directory, $filename)
    {
        $contents = '';

        // Check to see if we have a valid template file
        if (file_exists($directory . '/' . $filename))
        {
            // Store the file path
            $this->_file = $directory . '/' . $filename;

            // Get the file content
            ob_start();
            require $directory . '/' . $filename;
            $contents = ob_get_contents();
            ob_end_clean();
        }

        // Try to find a favicon by checking the template and root folder
        $icon = '/favicon.ico';

        foreach (array($directory, JPATH_BASE) as $dir)
        {
            if (file_exists($dir . $icon))
            {
                $path = str_replace(JPATH_BASE, '', $dir);
                $path = str_replace('\\', '/', $path);
                $this->addFavicon(JUri::base(true) . $path . $icon);
                break;
            }
        }

        return $contents;
    }


    public function loadRenderer($type)
    {
        $class = 'JDocumentRenderer' . $type;

        if (!class_exists($class))
        {
            $path = __DIR__ . '/renderer/' . $this->_type . '/' . $type . '.php';

            if (file_exists($path))
            {

                require_once $path;
            }
            else
            {

                throw new RuntimeException('Unable to load renderer class', 500);
            }
        }

        if (!class_exists($class))
        {
            return null;
        }

        $instance = new $class($this);

        return $instance;
    }

    public function getBuffer($type = null, $name = null, $attribs = array())
    {

        if ($type === null)
        {
            return parent::$_buffer;
        }

        $title = (isset($attribs['title'])) ? $attribs['title'] : null;

        if (isset(parent::$_buffer[$type][$name][$title]))
        {
            return parent::$_buffer[$type][$name][$title];
        }

        $renderer = $this->loadRenderer($type);

        if ($this->_caching == true && $type == 'modules')
        {
            $cache = JFactory::getCache('com_modules', '');
            $hash = md5(serialize(array($name, $attribs, null, $renderer)));
            $cbuffer = $cache->get('cbuffer_' . $type);

            if (isset($cbuffer[$hash]))
            {
                return JCache::getWorkarounds($cbuffer[$hash], array('mergehead' => 1));
            }
            else
            {
                $options = array();
                $options['nopathway'] = 1;
                $options['nomodules'] = 1;
                $options['modulemode'] = 1;

                $this->setBuffer($renderer->render($name, $attribs, null), $type, $name);
                $data = parent::$_buffer[$type][$name][$title];

                $tmpdata = JCache::setWorkarounds($data, $options);

                $cbuffer[$hash] = $tmpdata;

                $cache->store($cbuffer, 'cbuffer_' . $type);
            }
        }
        else
        {
            $this->setBuffer($renderer->render($name, $attribs, null), $type, $name, $title);
        }

        return parent::$_buffer[$type][$name][$title];
    }

    public function countModules($condition)
    {
        $operators = '(\+|\-|\*|\/|==|\!=|\<\>|\<|\>|\<=|\>=|and|or|xor)';
        $words = preg_split('# ' . $operators . ' #', $condition, null, PREG_SPLIT_DELIM_CAPTURE);

        if (count($words) === 1)
        {
            $name = strtolower($words[0]);
            $result = ((isset(parent::$_buffer['modules'][$name])) && (parent::$_buffer['modules'][$name] === false))
                ? 0 : count(JModuleHelper::getModules($name));

            return $result;
        }

        JLog::add('Using an expression in JDocumentHtml::countModules() is deprecated.', JLog::WARNING, 'deprecated');

        for ($i = 0, $n = count($words); $i < $n; $i += 2)
        {
            // Odd parts (modules)
            $name = strtolower($words[$i]);
            $words[$i] = ((isset(parent::$_buffer['modules'][$name])) && (parent::$_buffer['modules'][$name] === false))
                ? 0
                : count(JModuleHelper::getModules($name));
        }

        $str = 'return ' . implode(' ', $words) . ';';

        return eval($str);
    }

    public function setBuffer($content, $options = array())
    {


        // The following code is just for backward compatibility.
        if (func_num_args() > 1 && !is_array($options))
        {
            $args = func_get_args();
            $options = array();
            $options['type'] = $args[1];
            $options['name'] = (isset($args[2])) ? $args[2] : null;
            $options['title'] = (isset($args[3])) ? $args[3] : null;
        }

        parent::$_buffer[$options['type']][$options['name']][$options['title']] = $content;

        return $this;
    }


}
