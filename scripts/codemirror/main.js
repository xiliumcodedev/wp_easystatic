define(['require', 'exports', 'module',
    "codemirror", 
    "codemirror/scripts/js/javascript",
    "codemirror/scripts/js/xml",
    "codemirror/scripts/js/css",
    "codemirror/scripts/js/htmlmixed",
    "codemirror/scripts/js/matchbrackets",
    "codemirror/scripts/js/diff_match_patch",
    "codemirror/scripts/js/merge"], function(require, exports, module){

var wp_easystatic = module.config().wp_easystatic;

var CodeMirror = require('codemirror');

var editor;

if(wp_easystatic.tab == "static"){
    editor = CodeMirror.fromTextArea(document.getElementById("code-static-load"), {
         lineNumbers: true,
         lineWrapping: true,
         mode: "htmlmixed",
         addModeClass : true,
         matchBrackets: true,
         styleActiveLine: true,
         viewportMargin : 50,
         theme : 'material',
         gutters : ["CodeMirror-line", "breakpoints"]
    });

    editor.on("viewportChange", function(cm, n) {
      cm.setSize(null, 400)
      cm.addLineClass(10, "wrap", "line-wrap")
    });
}


exports.Editor = function(){
   return editor;
}

if(wp_easystatic.tab == "static"){

    var static_update_view = document.getElementById("static-update-view");

    var mergeView = CodeMirror.MergeView(static_update_view, {
        value: '',
        origLeft: '',
        lineNumbers: true,
        lineWrapping: true,
        mode: "htmlmixed",
        highlightDifferences: true,
        connect: "align",
        collapseIdentical: false,
        revertButtons : false,
        viewportMargin : 50,
        theme : 'material',
        gutters : ["CodeMirror-line", "breakpoints"]
     });
      
    mergeView.editor().on("viewportChange", function(cm, n) {
        cm.setSize(null, 500)
    })

    mergeView.leftOriginal().on("viewportChange", function(cm, n) {
        cm.setSize(null, 500)
    })

}

exports.checkStaticUpdate = function(_v, _l){
  mergeView.editor().setValue(_v)
  mergeView.leftOriginal().setValue(_l)
}  

exports.mergeView = function(){
    return mergeView;
}

})

