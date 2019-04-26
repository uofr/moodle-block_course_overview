<?php
require_once($CFG->libdir . "/externallib.php");
require_once(dirname(__FILE__) . '/locallib.php');

class block_course_overview_external extends external_api {

    /**
     * Add Favourite
     */
    public static function add_favourite_parameters() {
        return new external_function_parameters(array (
            'courseid' => new external_value(PARAM_INT)
        ));
    }

    public static function add_favourite($courseid) {
        $params = self::validate_parameters(self::add_favourite_parameters(), array('courseid' => $courseid));
        self::validate_context(context_course::instance($params['courseid']));
        block_course_overview_add_favourite($params['courseid']);

        $config = get_config('block_course_overview');
        return array(
            'courseid' => $params['courseid'],
            'keepfavourites' => $config->keepfavourites,
            'favouritealt' => get_string('unfavourite', 'block_course_overview'),
        );
    }

    public static function add_favourite_returns() {
        return new external_single_structure(array (
            'courseid' => new external_value(PARAM_INT),
            'keepfavourites' => new external_value(PARAM_BOOL),
            'favouritealt' => new external_value(PARAM_TEXT)
        ));
    }

    /**
     * Remove Favourite
     */
    public static function remove_favourite_parameters() {
        return new external_function_parameters(array (
            'courseid' => new external_value(PARAM_INT)
        ));
    }

    public static function remove_favourite($courseid) {
        $params = self::validate_parameters(self::remove_favourite_parameters(), array('courseid' => $courseid));
        self::validate_context(context_course::instance($params['courseid']));
        block_course_overview_remove_favourite($params['courseid']);

        $config = get_config('block_course_overview');
        return array(
            'courseid' => $params['courseid'],
            'keepfavourites' => $config->keepfavourites,
            'favouritealt' => get_string('unfavourite', 'block_course_overview')
        );
    }

    public static function remove_favourite_returns() {
        return new external_single_structure(array (
            'courseid' => new external_value(PARAM_INT),
            'keepfavourites' => new external_value(PARAM_BOOL),
            'favouritealt' => new external_value(PARAM_TEXT)
        ));
    }

}

