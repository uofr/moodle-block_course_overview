 define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {

   var SELECTORS = {
      OVIEW_TOGGLE :    '.overview-toggle',
      FAV_LINK :        '.notfav',
      UNFAV_LINK :      '.isfav',
      COURSE :          '.courseovbox',
      COURSES_TAB :     '#courses .course-list',
      FAVOURITES_TAB :  '#favourites .course-list',
      TAB_CONTENT :     '#course_overview_tabs',
   };

   function init(showText, hideText) {
      $(SELECTORS.TAB_CONTENT).on('click', SELECTORS.OVIEW_TOGGLE, {showText : showText, hideText : hideText}, toggleOverview);
      $(SELECTORS.TAB_CONTENT).on('click', SELECTORS.FAV_LINK, favouriteCourse);
      $(SELECTORS.TAB_CONTENT).on('click', SELECTORS.UNFAV_LINK, unfavouriteCourse);
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

   function favouriteCourse(event) {
      event.preventDefault();
      ajax.call(new Array({
         methodname: 'block_course_overview_add_favourite',
         args: {courseid : $(this).parent().parent().parent().attr('data-courseid')},
         done: favouriteCourseDone,
         fail: notification.exception
      }));
   }

   function favouriteCourseDone(response) {
      var course = $(SELECTORS.COURSES_TAB + ' ' + SELECTORS.COURSE + '[data-courseid=' + response.courseid + ']');
      var favLink = course.find(SELECTORS.FAV_LINK);
      var newIcon = $('<i></i> ').addClass('icon fa fa-fw fa-star').attr('title', response.favouritealt);

      favLink.html(newIcon);
      favLink.attr('alt', response.favouritealt);
      favLink.removeClass('notfav').addClass('isfav');

      // add course to favourites tab
      $(SELECTORS.FAVOURITES_TAB).append(course.clone());

      // if block_course_overview | keepfavourites is set to false, remove course from courses tab
      if (!response.keepfavourites) {
         course.remove();
      }
   }

   function unfavouriteCourse(event) {
      event.preventDefault();
      ajax.call(new Array({
         methodname: 'block_course_overview_remove_favourite',
         args: {courseid : $(this).parent().parent().parent().attr('data-courseid')},
         done: unfavouriteCourseDone,
         fail: notification.exception
      }));
   }

   function unfavouriteCourseDone(response) {
      var course = $(SELECTORS.FAVOURITES_TAB + ' ' + SELECTORS.COURSE + '[data-courseid=' + response.courseid + ']');
      var unfavLink = course.find(SELECTORS.UNFAV_LINK);
      var newIcon = $('<i></i> ').addClass('icon fa fa-fw fa-star-o').attr('title', response.favouritealt);
      
      // update course
      unfavLink.html(newIcon);
      unfavLink.attr('alt', response.favouritealt);
      unfavLink.removeClass('isfav').addClass('notfav');
      
      // if block_course_overview | keepfavourites is set to false, clone course from favourites into courses tab
      if (!response.keepfavourites) {
         $(SELECTORS.COURSES_TAB).append(course.clone());
      }
      // otherwise, update course in courses tab
      else {
         $(SELECTORS.COURSES_TAB + ' ' + SELECTORS.COURSE  + '[data-courseid=' + response.courseid + ']').replaceWith(course.clone());
      }

      // in either case, remove course from favourites tab
      course.remove();
   }

   return {init: init};

 });