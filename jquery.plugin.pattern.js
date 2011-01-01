/* jQuery Plugin Pattern 2.1
 * Author: Milan Adamovsky
 *
 * This pattern allows you to write jQuery plugins a lot faster, more consistently,
 * and less error prone.  Furthermore it allows namespacing, OOP integration, along
 * with other technical benefits.
 *
 * There are two "global" (but local) helper methods available throughout each plugin
 * and they are:
 *
 *   getPluginClass()
 *   getPluginName()
 *
 * The getPluginClass() returns the definition of the entire plugin code.  Usually
 * this is not needed by the plugin author (you), but in the rare event you would
 * need it, here it is.  It is used internally by the plugin pattern.
 *
 * The getPluginName() returns the name of the plugin's jQuery namespace as defined
 * by the initPlugin() method (which is something you, the author, defines).
 *
 * Attention to JSLint users: this code will not pass due to some advanced paradigms
 * in use, though efforts have been taken to minimize these.  Priority was given to
 * functionality and syntax correctness so that YUI Compressor can properly minify
 * the code.
 */

(function($)
  {
   initPlugin({
               name : 'asyncLoader'  // plugin name
              });

   //----plugin class -- BEGIN--------
   function getPluginClass()
    {
     return function (args)
      {
       // Version - directly exposed to be access via $(...).plugin.version
       this.version = '1.0';

       // Public Methods - wrappers containing a reference to the actual function
       this.someMethodA = someMethodA;   // non-chainable method
       this.someMethodB = _(someMethodB);    // chainable method

       this.rc = jQuerify;       // required plugin pattern code

       // getConfig - always used to contain class/plugin arguments
       function getConfig()
        {
         return undefined;       // we define this via setConfig()
        }

       // setConfig - always used to set class/plugin arguments, usually used indirectly via initConfig
       function setConfig(args)
        {
         getConfig = function()
                      {
                       return (args);
                      };
        }

       // initConfig - sets default values via jQuery's extend()
       function initConfig(args)
        {
         setConfig($.extend({
                             chainable : true,     // false - this allows us to do something like $(...).plugin(args).method()
                                                   //         this returns the object as the output
                                                   // true  - otherwise, by default, it will assume jQuery native functionality of chainability
                                                   //         this returns what jQuery expects for chaining
                             properties : {}       // any properties that you want to be passed to the plugin.
                            }, args));
         return (this);
        }

       //---[BEGIN YOUR CODE below]-------
       //   [Class methods / properties]

       function someMethodA(args)
        {
         alert('This is our non-chainable method!');

         return (this);    // we return this to allow classic OOP
        }

       function someMethodB(args)
        {
         alert('This is our chainable method!');

         // we don't have to return anything since the _() takes
         // care of chainability.
        }


       // initConfig - gets called to set defaults for plugin
       initConfig(args);   // Pattern code - usually just copy/paste

       // we put our logic after the method definitions to keep JSLint a
       // little happier.

       //---[END YOUR CODE above]----

       function _ (fn)      // local function that facilitates chainability
        {
         return function (args)
                 {
                  return this.prototype.rc(function ()
                                            {
                                             fn.call(this, args);
                                            });
                 };
        }

       function jQuerify(args)
        {
         if (typeof args == 'function')
          {
           return $.fn.curReturn.each(args);  // handles chaining of method
          }
         else
          {
           initConfig(args);

           return getConfig().chainable       // checks for chaining in main plugin
                   ? $.fn.curReturn           // returns chainability hook
                   : $.fn[getPluginName()];   // returns OOP object hook
                                              // getPluginName() is defined in initPlugin()
          }
        };
      };
    }

   if ($.fn.curReturn === undefined)          // checks if a plugin has already loaded environment
    {
     $.fn.extend({                            // if not then it extends jQuery object.
                  curReturn: null,            // declares placeholder
                  jQueryInit: jQuery.fn.init  // saves original jQuery init method
                 });

     $.fn.extend({                            // now we overwrite jQuery's internal init() so we
                                              // can intercept the selector and context.
                  init: function( selector, context )
                         {
                          return jQuery.fn.curReturn = new jQuery.fn.jQueryInit(selector, context);
                         }
                 });
    }

   function initPlugin(args)
    {
     getPluginName = function()
                       {
                        return args.name;    // sets getPluginName() to return jQuery plugin name
                       };

//     console && console.log(["Initialize", args.name, "plugin."].join(' '));

     try                                       // meat
      {
       var classCode = getPluginClass(),       // get a reference to the plugin OOP code
       _p = new classCode({});                 // instantiate plugin Class (which is OOP)
       $.fn[getPluginName()] = _p.rc;          // insert instantiated plugin into jQuery namespace
       $.extend($.fn[getPluginName()].prototype,_p);  // augment jQuery internals
       $.extend($.fn[getPluginName()],_p);            // same here.
      }
     catch (error)
      {
       alert(error);                           // in case something breaks let us know.
      }

    }

  })(jQuery);
