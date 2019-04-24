define(['jquery', 'core/ajax'], function($, ajax) {

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

    function favouriteCourse() {
        var ajaxCall = {
            methodname: 'block_course_overview_add_favourite',
            args: {courseid: $(this).attr('data-courseid')},
            done: response => console.log(response),
            fail: ex => console.log(ex)
        };
        ajax.call(new Array(ajaxCall));
    }

    function unfavouriteCourse() {
        var ajaxCall = {
            methodname: 'block_course_overview_remove_favourite',
            args: {courseid: $(this).attr('data-courseid')},
            done: response => console.log(response),
            fail: ex => console.log(ex)
        };
        ajax.call(new Array(ajaxCall));
    }

    return {init: init};

});