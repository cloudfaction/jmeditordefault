<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;

class PlgSystemJmEditorDefault extends CMSPlugin
{
    protected $app;
    
    public function onAfterDispatch()
    {
        if (!$this->app instanceof \Joomla\CMS\Application\CMSApplication || $this->app->isClient('cli')) {
            return;
        }

        // Check if we're in an edit view
        $option = $this->app->input->get('option');
        $view = $this->app->input->get('view');
        $layout = $this->app->input->get('layout');

        if (($option === 'com_content' && $view === 'article' && $layout === 'edit') ||
            ($option === 'com_modules' && ($view === 'module' || $view === 'modules') && $layout === 'edit')) {
            
            // Add JavaScript to handle editor switching
            $wa = $this->app->getDocument()->getWebAssetManager();
            $wa->addInlineScript($this->getEditorSwitchScript());
            
            // Add the editor switch button to toolbar
            $this->addEditorSwitchButton();
        }
    }

    protected function addEditorSwitchButton()
    {
        // Get available editors
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['element', 'name']))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('editors'))
            ->where($db->quoteName('enabled') . ' = 1');
        
        $editors = $db->setQuery($query)->loadObjectList();
        
        // Get current user's editor
        $user = Factory::getUser();
        $currentEditor = $user->getParam('editor', 'none');
        
        // Create dropdown options
        $options = [];
        foreach ($editors as $editor) {
            // Convert 'plg_editor_none' to 'None'
            $text = ($editor->element === 'none') ? 'None' : Text::_($editor->name);
            $selected = ($editor->element === $currentEditor) ? ' selected="selected"' : '';
            $options[] = [
                'value' => $editor->element,
                'text' => $text,
                'selected' => $selected
            ];
        }

        // Add the button with updated styling
        $toolbar = Toolbar::getInstance('toolbar');
        
        $dropdown = '<select id="editor-switch" class="form-select" style="width: 200px; height: 40px; padding: 2px 24px 2px 8px; margin: 5px 4px; font-size: 1rem;">';
        foreach ($options as $option) {
            $dropdown .= '<option value="' . $option['value'] . '"' . $option['selected'] . '>' . $option['text'] . '</option>';
        }
        $dropdown .= '</select>';

        $toolbar->appendButton('Custom', $dropdown, 'editor-switch');
    }

    protected function getEditorSwitchScript()
    {
        return <<<JS
            document.addEventListener('DOMContentLoaded', function() {
                var editorSwitch = document.getElementById('editor-switch');
                if (editorSwitch) {
                    editorSwitch.addEventListener('change', function() {
                        var editor = this.value;
                        var formData = new FormData();
                        formData.append('editor', editor);
                        formData.append('task', 'switchEditor');
                        
                        // Save current content using proper Joomla API
                        var currentContent = '';
                        try {
                            var activeEditor = Joomla.getOptions('joomla.jform.editor');
                            if (activeEditor) {
                                currentContent = Joomla.editors.get(activeEditor).getValue();
                            } else {
                                // Fallback to getting the first editor on the page
                                var editorElements = document.querySelectorAll('.js-editor-none');
                                if (editorElements.length > 0) {
                                    var editorId = editorElements[0].id;
                                    currentContent = Joomla.editors.get(editorId).getValue();
                                }
                            }
                        } catch(e) {
                            console.log('Could not get editor content:', e);
                        }
                        
                        formData.append('content', currentContent);
                        
                        fetch('index.php?option=com_ajax&plugin=jmeditordefault&group=system&format=json', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            }
                        });
                    });
                }
            });
        JS;
    }

    public function onAjaxJmeditordefault()
    {
        $input = $this->app->input;
        $task = $input->get('task');
        
        if ($task === 'switchEditor') {
            $editor = $input->get('editor', '', 'string');
            $content = $input->get('content', '', 'raw');
            
            // Store the content temporarily in the session
            $this->app->getSession()->set('editor_switch_content', $content);
            
            // Set the new editor
            $user = Factory::getUser();
            $user->setParam('editor', $editor);
            $user->save(true);
            
            return ['success' => true];
        }
        
        return ['success' => false];
    }
} 