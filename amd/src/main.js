define(['jquery', 'core/ajax', 'core/notification'], function($, ajax, notification) {

    function init() {
        $('.collapse').on('show.bs.collapse', collapseShow);
        $('.collapse').on('hidden.bs.collapse', collapseHide);
        $('.notfav').click(favouriteCourse);
        $('.isfav').click(unfavouriteCourse);
    }

    function collapseShow() {
        $(this).parent().parent().find('.activity-description').show();
        $(this).parent().parent().find('.course-detail-collapse').text('Hide');
    }

    function collapseHide() {
        $(this).parent().parent().find('.activity-description').hide();
        $(this).parent().parent().find('.course-detail-collapse').text('Show');
    }

    function favouriteCourse(event) {
        event.preventDefault();
        var ajaxCall = {
            methodname: 'block_course_overview_add_favourite',
            args: {courseid: $(this).attr('data-courseid')},
            done: favouriteDone,
            fail: notification.ex
        };
        ajax.call(new Array(ajaxCall));
    }

    function favouriteDone(response) {
        var course = $('#courses .courseovbox[data-courseid=' + response.courseid + ']');
        var favourites_list = $('#favourites .course-list');
        var favlink = course.find('a.favourite_icon');
        var favicon = favlink.find('i');
        
        favlink.attr('alt', response.favouritealt);
        favlink.removeClass('notfav').addClass('isfav');
        favicon.removeClass('fa-star-o').addClass('fa-star');

        if (favourites_list.find('.courseovbox').length == 0) {
            favourites_list.html(course.clone());
        }
        else {
            favourites_list.append(course.clone());
        }

        if (!response.keepfavourites) {
            course.remove();
        }
        $('.isfav').off('click', unfavouriteCourse);
        $('.isfav').click(unfavouriteCourse);
    }

    function unfavouriteCourse() {
        event.preventDefault();
        var ajaxCall = {
            methodname: 'block_course_overview_remove_favourite',
            args: {courseid: $(this).attr('data-courseid')},
            done: unfavouriteDone,
            fail: ex => console.log(ex)
        };
        ajax.call(new Array(ajaxCall));
    }

    function unfavouriteDone(response) {
        var course = $('#courses .courseovbox[data-courseid=' + response.courseid + ']');
        var favcourse = $('#favourites .courseovbox[data-courseid=' + response.courseid + ']');
        var course_list = $('#courses .course-list');
        var favlink = favcourse.find('a.favourite_icon');
        var favicon = favlink.find('i');
        
        favlink.attr('alt', response.favouritealt);
        favlink.removeClass('isfav').addClass('notfav');
        favicon.removeClass('fa-star').addClass('fa-star-o');

        if (response.keepfavourites) {
            course.replaceWith(favcourse.clone());
            favcourse.remove();
        }
        else {
            if (course_list.find('.courseovbox').length == 0) {
                course_list.html(favcourse.clone());
            }
            else {
                course_list.append(favcourse.clone());
            }
            favcourse.remove();
        }
        $('.notfav').off('click', favouriteCourse);
        $('.notfav').click(favouriteCourse);
    }

    return {init: init};

});