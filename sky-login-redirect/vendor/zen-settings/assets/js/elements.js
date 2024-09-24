"use strict"
/**
 * Startswith
 */
function startsWith(str, word) {
  return str.lastIndexOf(word, 0) === 0;
}

/**
 * CodeMirror cursor
 * 
 * @returns null
 */
function refreshCodeMirrors(plan) {

  let readonly = false;
  const fs_bodyClassList = Array.from(document.body.classList);
  const fs_planClass = fs_bodyClassList.find(className => className.startsWith('plan-'));
  if (fs_planClass === 'plan-free' || fs_planClass === 'plan-starter') {
    readonly = 'nocursor';
  }
  /**
   * CodeMirror textareas
   */
  const textareas = document.querySelectorAll('.codemirror-js textarea, textarea.codemirror-js');
  textareas.forEach(function (e) {
    if (e.style.display !== 'none') {
      let editorSettings = wp.codeEditor.defaultSettings ? { ...wp.codeEditor.defaultSettings } : {};
      editorSettings.codemirror = {
        ...editorSettings.codemirror,
        indentUnit: 2,
        tabSize: 2,
        mode: 'text/html',
        autoRefresh: true,
        readOnly: readonly
      };
      wp.codeEditor.initialize(e, editorSettings);
    }
  });
};

document.addEventListener('DOMContentLoaded', function () {
  refreshCodeMirrors();
});