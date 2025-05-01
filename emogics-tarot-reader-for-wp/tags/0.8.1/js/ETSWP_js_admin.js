//deactivate warn
window.onload = function(){
        var ETSWP_deactivate_dom = document.querySelector('.deactivate a[href*="plugin=Emogic-Tarot-Reader-Plugin-for-Wordpress"]');
        //document.querySelector('.deactivate a[href*="plugin=Emogic-Tarot-Reader-Plugin-for-Wordpress"]').addEventListener('click', function(event){
        ETSWP_deactivate_dom.addEventListener('click', function(event){
            event.preventDefault();
			//document.querySelector('[data-slug="plugin-name-here"] .deactivate a')
            //var urlRedirect = document.querySelector('.deactivate a[href*="plugin=Emogic-Tarot-Reader-Plugin-for-Wordpress"]').getAttribute('href');
            var urlRedirect = ETSWP_deactivate_dom.getAttribute('href');
            if (confirm('Are you sure you want to disable Emogic-Tarot-Reader-Plugin-for-Wordpress? It will remove all the images and pages that were installed by this plugin. You may want to backup first.')) {
                window.location.href = urlRedirect;
            } else {
                console.log('Emogic-Tarot-Reader-Plugin-for-Wordpress disable aborted by user');
            }
        });
};
