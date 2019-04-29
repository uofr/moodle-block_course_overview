/**
 * What does course overview need to do?
 *  > Show/Hide notifications.
 *      > When user presses Show or Hide button respectively.
 *      > Show or Hide Overview Description and Overview Text, change button text to Show or Hide
 *      > Can probably be done just by swapping a css class (animation through css).
 *      > Show or Hide text needed, and a css class.
 *      > Pass show and Hide text through constructor?
 *  > Favourite/Un-Favourite through AJAX.
 *      > When user presses Favourite or Un-Favourite button respectively.
 */

 define(['jquery'], function($) {

   var SELECTORS = {
      OVIEW_TOGGLE : '.overview-toggle',
   };

   function init(showText, hideText) {
      $(SELECTORS.OVIEW_TOGGLE).click({showText : showText, hideText : hideText}, toggleOverview);
   }

   function toggleOverview(event) {
      event.preventDefault();

      var toggleButton = $(this);
      var course = $(this).parent().parent().parent().parent();
      var overviewArea = course.find('.activity-overviews');

      overviewArea.toggleClass('overview-hide');

      if (overviewArea.hasClass('overview-hide')) {
         toggleButton.text(event.data.showText);
      }
      else {
         toggleButton.text(event.data.hideText);
      }
   }

   return {init: init};

 });