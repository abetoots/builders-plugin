<?php

namespace Builders_Plugin\Constants;

/**
 * Gym user types keys
 */
const GYM_MEMBER = "gym_member";
const GYM_TRAINER = "gym_trainer";
const GYM_ADMIN = "gym_admin";

/**
 * Gym member meta keys / WPGraphQl keys
 */
const JWT_AUTH_EXPIRATION = "jwtAuthExpiration";
const REFRESH_TOKEN = "refreshToken";
const AUTH_TOKEN = "authToken";
const FULL_NAME = "full_name";
const MEMBERSHIP_DURATION = "membership_duration";
const IS_STUDENT = "is_student";
const GYM_ROLE = "gym_role";
const BRANCH = "branch";
const GYM_MEMBER_FIELDS = "full_name
                            is_student
                            membership_duration
                            id
                            gym_role
                            branch
                            userId
                            ";

const THIRTY_DAYS = '30 days';
const NINETY_DAYS = '90 days';
const HALF_YEAR = '180 days';
const ONE_YEAR = '1 year';

const GYM_USER_ALLOWED_EDITABLE_FIELDS = [FULL_NAME, MEMBERSHIP_DURATION];

/**
 * Shared
 */
const PLUGIN_PREFIX = "builders_plugin";
const ACTION_REGISTER_GYM_MEMBER =
"builders_do_action_register_gym_member";
const ACTION_AJAX_REGISTER_GYM_MEMBER =
"builders_do_action_register_gym_member_app";
const VALIDDATEFORMAT = "Ymd";
