(function() {
	if( typeof window.SalsaPressVars === 'undefined' ) return
	tinymce.create('tinymce.plugins.salsa', {
		init : function(ed, url) {
			var t = this;

			t.url = url;
			t._createButtons();


			ed.onMouseDown.add(function(ed, e) {
				if ( e.target.nodeName == 'IMG' && ed.dom.hasClass(e.target, 'salsa') )
					ed.plugins.wordpress._showButtons(e.target, 'wp_gallerybtns');
			});

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t._do_salsa(o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = t._get_salsa(o.content);
			});
		},

		_do_salsa : function(co) {
			return co.replace(/\[salsa([^\]]*)\]/g, function(a,b){
				return '<img src="'+SalsaPressVars.stylesheet_directory+'images/salsaembed.png" style="border: 1px dashed #888;" class="salsa mceItem" title="salsa'+tinymce.DOM.encode(b)+'" />';
			});
		},

		_get_salsa : function(co) {

			function getAttr(s, n) {
				n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
				return n ? tinymce.DOM.decode(n[1]) : '';
			};

			return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function(a,im) {
				var cls = getAttr(im, 'class');

				if ( cls.indexOf('salsa') != -1 )
					return '<p>['+tinymce.trim(getAttr(im, 'title'))+']</p>';

				return a;
			});
		},

		_createButtons : function() {
			var t = this, ed = tinyMCE.activeEditor, DOM = tinymce.DOM, editButton, dellButton;

			DOM.remove('wp_gallerybtns');

			DOM.add(document.body, 'div', {
				id : 'wp_gallerybtns',
				style : 'display:none;'
			});

			dellButton = DOM.add('wp_gallerybtns', 'img', {
				src : SalsaPressVars.stylesheet_directory+'images/delete.png',
				id : 'wp_delgallery',
				width : '24',
				height : '24',
				title : ed.getLang('wordpress.delgallery')
			});

			dellButton.onclick = function(e) {
				var ed = tinyMCE.activeEditor, el = ed.selection.getNode();

				if ( el.nodeName == 'IMG' && ed.dom.hasClass(el, 'salsa') ) {
					ed.dom.remove(el);

					ed.execCommand('mceRepaint');
					return false;
				}
			}
		},

		getInfo : function() {
			return {
				longname : 'Gallery Settings',
				author : 'WordPress',
				authorurl : 'http://wordpress.org',
				infourl : '',
				version : "1.0"
			};
		}
	});

	tinymce.PluginManager.add('salsa', tinymce.plugins.salsa);
})();
