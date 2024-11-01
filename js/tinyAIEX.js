(function() {
    var ai_tools_history = [] ;
    var ai_tools_history_index = -1 ;        

    tinymce.PluginManager.add( 'ai_tools_menu', function( editor, url ) {
        var plugin_menu = [] ;
        var tinyaiex_plugin_options_array = JSON.parse(tinyaiex_plugin_options) ;
        for (var i=0; i < tinyaiex_plugin_options_array.length; i++) {
            var tinyaiex_option = tinyaiex_plugin_options_array[i] ;
            plugin_menu.push({
                text: tinyaiex_option.title,
                value: tinyaiex_option.name,
                onclick: function(){
                  var editor_id = getEditorId(editor) ;
                  jQuery("#" + editor_id).LoadingOverlay("show");                      
                  var data = {
                      'action': 'tinyAIEX_command',
                      'nonce': tinyaiex_ajax_nonce,
                      'cmd': this.settings.value,
                      'content': editor.getContent(),
                  };
                  jQuery.post(ajaxurl, data, function(response) {
                      if (response.code == "1") {
                        ai_tools_history.push(editor.getContent()) ;
                        ai_tools_history_index++ ;
                        editor.setContent(response.message);
                      }
                      else
                      {
                        alert(response.message) ;
                      }
                      jQuery("#" + editor_id).LoadingOverlay("hide");                      
                  }, "json");                          
                }
            });            
        }    

        // Add Button to Visual Editor Toolbar        
        editor.addButton("ai_tools_menu",{

            text: tinyaiex_plugin_texts.tinyaiex_text_ai_tools,
            tooltip: tinyaiex_plugin_texts.tinyaiex_text_available_ai_tools,
            type:"menubutton",
            menu:plugin_menu
        });

        editor.addButton('ai_tools_undo', {
            title: tinyaiex_plugin_texts.tinyaiex_text_undo,
            image: tinyaiex_plugin_url + 'images/undo_icon.png',
            cmd: 'ai_tools_undo',
        });

        // Add Command when Button Clicked
        editor.addCommand('ai_tools_undo', function() {
            if (ai_tools_history_index > -1) {
                editor.setContent(ai_tools_history.pop());
                ai_tools_history_index-- ;
            }
        });         


    });
})();

function getEditorId(editor) {
    var editor_id = "" ;
    if (typeof editor.bodyElement != "undefined") {                        
        editor_id = editor.bodyElement.id;                      
    }
    else if (typeof editor.id != "undefined") {
        editor_id = editor.iframeElement.id;                                         
    }
    return editor_id ;
}