var $ = jQuery.noConflict();

jQuery(function() {
    var max_items = 3 ;
    if (tinyaiex_mode == "premium") {
        max_items = null ;
    }
    var selects = jQuery('#tinyaiex_setting_tiny_options').selectize({
        hideSelected: true,
        placeholder: js_szovegek.tinyaiex_setting_tiny_options_placeholder,
        plugins: ["remove_button"],        
        maxItems: max_items,
        valueField: 'name',
        labelField: 'title',
        searchField: 'title',     
        options: JSON.parse(jQuery("#tinyaiex_setting_available_options").val()),
        items: JSON.parse(jQuery("#tinyaiex_setting_selected_options").val()),
    });     
    var available_commands_control = selects[0].selectize;        

    var remove_command_selects = jQuery('#tinyaiex_setting_remove_options').selectize({
        hideSelected: true,
        placeholder: js_szovegek.tinyaiex_setting_remove_options_placeholder,
        plugins: ["remove_button"],        
        maxItems: null,
        valueField: 'name',
        labelField: 'title',
        searchField: 'title',     
        options: JSON.parse(jQuery("#tinyaiex_setting_available_options").val()),
    });     
    var remove_commands_control = remove_command_selects[0].selectize;        


    jQuery("#add_command").on("click", function() {
        if (jQuery("#new_command").val() != "" && jQuery("#new_command_name").val() != "") {
            var regex = /[^A-Za-z0-9]/g;
            var new_command = jQuery("#new_command").val() ;
            var new_command_name = jQuery("#new_command_name").val() ;
            var sanitized_new_command_name = new_command_name.replace(regex, "");
            var data = {
                'action': 'tinyAIEX_add_command',
                'nonce': tinyaiex_ajax_nonce,
                'command_name': sanitized_new_command_name,
                'command_title': new_command_name,
                'command_cmd': new_command,
            };
            jQuery.post(ajaxurl, data, function(response) {
                if (response.code == "1") {
                    available_commands_control.addOption({
                        name: sanitized_new_command_name,
                        title: new_command_name,                
                    });
                    var available_options = JSON.parse(jQuery("#tinyaiex_setting_available_options").val()) ;
                    available_options.push({"name": sanitized_new_command_name, "title": new_command_name, "cmd": new_command}) ;
                    jQuery("#tinyaiex_setting_available_options").val(JSON.stringify(available_options)) ;
                    available_commands_control.addItem(sanitized_new_command_name);
                    available_commands_control.refreshOptions();
                    jQuery("#new_command").val("") ;
                    jQuery("#new_command_name").val("") ;
                }
                else
                {
                  alert(response.message) ;
                }
            }, "json");                    
        }
    });    

    jQuery("#remove_commands").on("click", function() {
        if (jQuery('#tinyaiex_setting_remove_options')[0].selectize.items.length > 0) {
            var data = {
                'action': 'tinyAIEX_remove_commands',
                'nonce': tinyaiex_ajax_nonce,
                'commands': jQuery('#tinyaiex_setting_remove_options')[0].selectize.items,
            };
            jQuery.post(ajaxurl, data, function(response) {
                if (response.code == "1") {
                    var removed = jQuery('#tinyaiex_setting_remove_options')[0].selectize.items ;
                    var removed_count = removed.length ;
                    var available_options = JSON.parse(jQuery("#tinyaiex_setting_available_options").val()) ;
                    var new_available_options = new Array() ;
                    for (var j = 0; j < available_options.length; j++) {
                        if (removed.indexOf(available_options[j].name) < 0) {
                            new_available_options.push(available_options[j]) ;
                        }
                    }
                    jQuery("#tinyaiex_setting_available_options").val(JSON.stringify(new_available_options)) ;
                    for (var i = 0; i < removed_count; i++) {
                        available_commands_control.removeOption(removed[0], true) ;
                        remove_commands_control.removeOption(removed[0], true) ;
                    }
                    available_commands_control.refreshOptions();                    
                    remove_commands_control.refreshOptions(false) ;
                    remove_commands_control.clear() ;
                    alert(response.message) ;
                }
                else
                {
                  alert(response.message) ;
                }
            }, "json");                    
        }
    });    


});