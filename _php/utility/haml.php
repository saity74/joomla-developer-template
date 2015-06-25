<?php
defined('JPATH_PLATFORM') or die;

abstract class JHaml
{

    public static function _($view)
    {

        $component = JApplicationHelper::getComponentName();
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();

        $hamlFile = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . $view->getName() . '/' . $view->getLayout().'.haml';


        if (file_exists($hamlFile))
        {
            $viewHaml = $view;
            if (func_num_args() > 1)
            {
                $properties = func_get_arg(1);
                if (is_array($properties))
                {
                    foreach($properties as $prop => $value)
                    {
                        $viewHaml->{$prop} = $value;
                    }
                }
            }

            $contents = '';

            try {
                ob_start();
                $hamlExecutor = $document->getHamlExecutor();
                $hamlExecutor->display($hamlFile, ['view' => $viewHaml]);
                $contents = ob_get_contents();
                ob_end_clean();

            } catch (MtHaml\Exception $e) {
                echo "Failed to execute template: ", $e->getMessage(), "\n";
            }

            echo $contents;
            return true;
        }

        return false;
    }

    public static function display($tpl, array $variables = [], $absolute = false)
    {
        $component = JApplicationHelper::getComponentName();
        $app = JFactory::getApplication();
        $document = JFactory::getDocument();

        $fileName = preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl);

        $viewName = $app->input->get('view');
        $layoutName = $app->input->get('layout');

        if (!$absolute)
            $file = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $component . '/' . $viewName . '/' . $layoutName.'_'.$fileName.'.haml';
        else
            $file = JPATH_THEMES . '/' . $app->getTemplate() . '/'. $tpl;

        jimport('joomla.filesystem.path');

        // Clean up the path
        $file = JPath::clean($file);

        if (!file_exists($file))
        {
            return 'Template file note found!';
        }

        $hamlExecutor = $document->getHamlExecutor();
        $content = $hamlExecutor->display($file, $variables);
        if ($content)
        {
            return $content;
        }
    }
}