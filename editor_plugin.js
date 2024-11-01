function wpols_plugin() {
    return "[WP_online_store]";
}
 
(function() {
    tinymce.create('tinymce.plugins.wpolsplugin', {
 
        init : function(ed, url){
            ed.addButton('wpolsplugin', {
            title : 'Insert WP Online Store Shortcode',
                onclick : function() {
                    ed.execCommand(
                    'mceInsertContent',
                    false,
                    wpols_plugin()
                    );
                },
                image: url + "/btn.jpg"

            });
        }
    });
 
    tinymce.PluginManager.add('wpolsplugin', tinymce.plugins.wpolsplugin);
 
})();