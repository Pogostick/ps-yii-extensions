/**
 * Copyright (c) 2009, Nathan Bubna
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * This plugin extends the loading plugin to automatically deal with situations
 * where you are masking an element that has children which overflow the
 * parent element's boundaries in browsers that use a box model.  In IE,
 * this plugin is idle, as there is no overflow like that. 
 *
 * There are two ways to handle this: stretch the mask to cover the overflow
 * or use css to hide the overflow.
 *
 * By default, this will leave the overflow settings alone and instead calculate the
 * size of mask needed to cover it all.  When there are a great many elements,
 * this becomes quite slow, so use this wisely (if at all).
 *
 * If you have certain elements where the stretch calculations are slowing things
 * down, then you can set the 'maskStretch' option to false.
 * The plugin will instead set the parent element's overflow to 'hidden'
 * and restoring it after the mask is gone.  You can change the alternate
 * overflow setting with the 'altOverflow' option (per-call or globally).
 *
 * Of course, this alternate functionality can be easily achieved by adding
 * the following CSS rule to your stylesheet:
 *
 *  .loading-masked { overflow: hidden; }
 *
 * If you have no interest in keeping the overflow visible, you should probably
 * avoid using this plugin and instead use the CSS rule.
 *
 * Contributions, bug reports and general feedback on this is welcome.
 *
 * @version 1.3+
 * @name loading-overflow
 * @cat Plugins/loading-overflow
 * @author Nathan Bubna
 */
;(function($) {
    
    var L = $.loading;
    L.maskStretch = true;
    L.altOverflow = 'hidden';

    var createMask = L.createMask;
    L.createMask = function(opts) {
        var mask = createMask.apply(this, arguments);
        if ($.boxModel && !opts.maskStretch) {
            opts.parent.oldOverflow = opts.parent.css('overflow') || 'auto';
            opts.parent.css('overflow', opts.altOverflow)
        }
        return mask;
    };
    var off = L.off;
    L.off = function(old, opts) {
        if (old.parent.oldOverflow) {
            old.parent.css('overflow', old.parent.oldOverflow);
        }
        off.apply(this, arguments);
    };

    var elementBox = L.elementBox;
    L.elementBox = function(e, opts) {
        var box = elementBox(e, opts);
        if ($.boxModel && opts.maskStretch) {
            var b = box.top + box.height, r = box.left + box.width;
            e.children().each(function() {
                var kid = opts.elementBox($(this), opts),
                    kb = kid.top + kid.height, kr = kid.left + kid.width;
                if (kb > b) b = kb;
                if (kr > r) r = kr;
            });
            box.height = b - box.top;
            box.width = r - box.left;
        }
        return box;
    };

})(jQuery);
