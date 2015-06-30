<?php
    function modChrome_slider($module, $params, $attribs)
    {

        $wrapperClass = htmlspecialchars($params->get('myslider'));

        $html = '';

        if (!empty ($module->content)) {

            $html .= '<div class="mod-slider-wrapper '.$wrapperClass.'">';
            $html .= '<div class="viewport">';
            $html .= '<ul class="overview unstyled">';

            $html .= $module->content;

            $html .= '</ul>';
            $html .= '</div>';
            $html .= '</div>';

        }

        echo $html;
    }

    function modChrome_default($module, &$params, &$attribs)
    {
        $moduleTag     = $params->get('module_tag', 'div');
        $bootstrapSize = (int) $params->get('bootstrap_size', 0);
        $moduleClass   = $bootstrapSize != 0 ? ' col-md-' . $bootstrapSize : '';
        $headerTag     = htmlspecialchars($params->get('header_tag', 'h3'));
        $headerClass   = htmlspecialchars($params->get('header_class', ''));
        $moduleclass_sfx = $params->get('moduleclass_sfx');
        if ($module->content)
        {
            echo '<' . $moduleTag . ' class="module-default-wrapper mod_'. $module->name . htmlspecialchars($moduleclass_sfx) .' '.$moduleClass. '">';
            if ($module->showtitle)
            {
                echo '<div class="module-header"><' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '></div>';
            }
            echo $module->content;
            echo '</' . $moduleTag . '>';
        }
    }

    function modChrome_menu($module, &$params, &$attribs)
    {
        $moduleTag     = $params->get('module_tag', 'div');
        $bootstrapSize = (int) $params->get('bootstrap_size', 0);
        $moduleClass   = $bootstrapSize != 0 ? ' col-md-' . $bootstrapSize : '';
        $headerTag     = htmlspecialchars($params->get('header_tag', 'h3'));
        $headerClass   = htmlspecialchars($params->get('header_class', 'page-header'));
        $menuID        = htmlspecialchars($params->get('tag_id', 'navbar-collapse'));

        if ($module->content)
        {
            echo '<' . $moduleTag . ' class="' . htmlspecialchars($params->get('moduleclass_sfx')) . $moduleClass . '">';

            if ($module->showtitle)
            {
                echo '<' . $headerTag . ' class="' . $headerClass . '">' . $module->title . '</' . $headerTag . '>';
            }

            echo '<div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#'.$menuID.'">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                </button>
                </div>';

            echo $module->content;
            echo '</' . $moduleTag . '>';
        }
    }
?>
