-- CLSU PPSDS Construction System Database Dump
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `companies`;
CREATE TABLE `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `companies` (`id`, `name`, `code`, `contact_person`, `contact_email`, `contact_phone`, `address`, `status`, `logo_url`, `created_at`, `updated_at`) VALUES ('1', 'CLSU - Physical Plant & Site Development Services', 'PPSDS', 'Atty. Sofia Reyes (PPSDS Director)', 'ppsds@clsu.edu.ph', '+63 44 456 0107', 'PPSDS Bldg., CLSU Main Campus, Science City of Muñoz, Nueva Ecija', 'Active', NULL, '2026-10-04 09:49:10', '2026-10-04 09:49:10');
INSERT INTO `companies` (`id`, `name`, `code`, `contact_person`, `contact_email`, `contact_phone`, `address`, `status`, `logo_url`, `created_at`, `updated_at`) VALUES ('2', 'try contractor', 'ASFB', 'aldwin', 'aldwin@mail.com', '345673456', 'pamaldan', 'Active', NULL, '2026-10-04 09:56:00', '2026-10-04 09:56:00');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'project_engineer',
  `initials` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `custom_permissions` json DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_company_id_foreign` (`company_id`),
  CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`id`, `company_id`, `name`, `email`, `role`, `initials`, `status`, `custom_permissions`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('1', '1', 'admin', 'admin@vertical.ph', 'admin', 'SR', 'Active', '{\"tab_audit\": true, \"tab_users\": true, \"tab_progress\": true, \"tab_projects\": true, \"tab_companies\": true, \"tab_dashboard\": true, \"tab_scheduling\": true, \"action_reschedule\": true, \"tab_weather_config\": true, \"action_export_audit\": true, \"action_manage_users\": true, \"action_edit_progress\": true, \"tab_activity_library\": true, \"action_manage_projects\": true, \"action_manage_companies\": true, \"action_continue_same_day\": true, \"action_manage_activities\": true}', NULL, '$2y$12$HM1zcUVQUg2qNW2IVIgAV.kTNBHJs7r5mBZZPjFiI/rXxOiIdgDrO', 'nA4GMitaPkA7GyDJ3nWlbpOFEmyC1Bs4ZLgquZVzDI4YRD8ezvVOAZw03D6G', '2026-10-04 09:49:10', '2026-10-05 01:53:59');
INSERT INTO `users` (`id`, `company_id`, `name`, `email`, `role`, `initials`, `status`, `custom_permissions`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('2', '1', 'Engr. Juan Dela Cruz', 'engineer@vertical.ph', 'project_engineer', 'JD', 'Active', NULL, NULL, '$2y$12$Vj3VWTBcySO57PiE9KRdMOfbiXnqnwt7HkI8LVCc/ZSuutooTBM62', 'MptttWucCe6xu14jfMHqjlIBQN1p5TH6kLDMO9cYaIfIELCk24oV0cWShLI0', '2026-10-04 09:49:10', '2026-10-04 09:49:10');
INSERT INTO `users` (`id`, `company_id`, `name`, `email`, `role`, `initials`, `status`, `custom_permissions`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('3', '1', 'Carlos Mendoza', 'supervisor@vertical.ph', 'site_supervisor', 'CM', 'Active', NULL, NULL, '$2y$12$WKJTVE/5.yEZ8euJYagn9OnyDCqyWrgobF4m2RBeDDCwSI5wmHCdy', NULL, '2026-10-04 09:49:10', '2026-10-04 09:49:10');
INSERT INTO `users` (`id`, `company_id`, `name`, `email`, `role`, `initials`, `status`, `custom_permissions`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES ('4', '1', 'Engr. Ramon Santos', 'contractor@vertical.ph', 'contractor', 'RS', 'Active', NULL, NULL, '$2y$12$WlVW2PrqFkbrl1jn939zged0dvSwMYd0yMlsf14kxVlqt73T2ZdAq', NULL, '2026-10-04 09:49:11', '2026-10-04 09:49:11');

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CLSU Campus, Science City of Muñoz, Nueva Ecija',
  `latitude` decimal(10,7) NOT NULL DEFAULT '15.7144000',
  `longitude` decimal(10,7) NOT NULL DEFAULT '120.9307000',
  `storeys` int NOT NULL DEFAULT '5',
  `duration_weeks` int NOT NULL DEFAULT '12',
  `start_date` date DEFAULT NULL,
  `target_completion_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'In Progress',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_code_unique` (`code`),
  KEY `projects_company_id_foreign` (`company_id`),
  CONSTRAINT `projects_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects` (`id`, `company_id`, `name`, `code`, `location`, `latitude`, `longitude`, `storeys`, `duration_weeks`, `start_date`, `target_completion_date`, `status`, `created_at`, `updated_at`) VALUES ('1', '1', 'try project', 'CLSU-BLDG-2026-0101', 'CLSU Campus, Science City of Muñoz, Nueva Ecija', '15.7144000', '120.9307000', '69', '12', NULL, NULL, 'Active', '2026-10-04 09:58:31', '2026-10-04 09:58:31');
INSERT INTO `projects` (`id`, `company_id`, `name`, `code`, `location`, `latitude`, `longitude`, `storeys`, `duration_weeks`, `start_date`, `target_completion_date`, `status`, `created_at`, `updated_at`) VALUES ('2', '2', 'try 23', 'CLSU-BLDG-2026-02', 'CLSU Campus, Science City of Muñoz, Nueva Ecija', '15.7144000', '120.9307000', '20', '12', NULL, NULL, 'Active', '2026-10-05 01:37:24', '2026-10-05 01:37:24');

DROP TABLE IF EXISTS `master_activities`;
CREATE TABLE `master_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Structural',
  `default_duration_days` int NOT NULL DEFAULT '3',
  `max_rain_probability` decimal(5,2) NOT NULL DEFAULT '40.00',
  `max_rain_volume_mm` decimal(5,2) NOT NULL DEFAULT '2.50',
  `max_wind_speed_kmh` decimal(5,2) NOT NULL DEFAULT '40.00',
  `max_temperature_c` decimal(5,2) NOT NULL DEFAULT '36.00',
  `safety_trigger` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Rainfall > 3mm/hr',
  `predecessor_hint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_activities_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `master_activities` (`id`, `code`, `name`, `category`, `default_duration_days`, `max_rain_probability`, `max_rain_volume_mm`, `max_wind_speed_kmh`, `max_temperature_c`, `safety_trigger`, `predecessor_hint`, `description`, `created_at`, `updated_at`) VALUES ('1', 'ACT-7575', 'buhos', 'Structural', '5', '50.00', '2.50', '40.00', '36.00', 'Rainfall > 3mm/hr (Safety Warning)', 'Preceding Milestone Activity', 'try notes', '2026-10-04 09:59:46', '2026-10-04 09:59:46');
INSERT INTO `master_activities` (`id`, `code`, `name`, `category`, `default_duration_days`, `max_rain_probability`, `max_rain_volume_mm`, `max_wind_speed_kmh`, `max_temperature_c`, `safety_trigger`, `predecessor_hint`, `description`, `created_at`, `updated_at`) VALUES ('3', 'ACT-1267', 'try', 'Structural', '5', '40.00', '2.50', '40.00', '36.00', 'Rainfall > 3mm/hr (Safety Warning)', 'Preceding Milestone Activity', NULL, '2026-10-04 10:25:08', '2026-10-04 10:25:08');

DROP TABLE IF EXISTS `schedules`;
CREATE TABLE `schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `master_activity_id` bigint unsigned DEFAULT NULL,
  `predecessor_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_days` int NOT NULL DEFAULT '2',
  `baseline_start_date` date NOT NULL,
  `baseline_end_date` date NOT NULL,
  `current_start_date` date NOT NULL,
  `current_end_date` date NOT NULL,
  `feasibility` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Feasible',
  `has_weather_alert` tinyint(1) NOT NULL DEFAULT '0',
  `weather_rule_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Rain < 40%, Wind < 40km/h',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Scheduled',
  `continued_same_day` tinyint(1) NOT NULL DEFAULT '0',
  `resume_time` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mitigation_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedules_project_id_foreign` (`project_id`),
  KEY `schedules_master_activity_id_foreign` (`master_activity_id`),
  KEY `schedules_predecessor_id_foreign` (`predecessor_id`),
  CONSTRAINT `schedules_master_activity_id_foreign` FOREIGN KEY (`master_activity_id`) REFERENCES `master_activities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedules_predecessor_id_foreign` FOREIGN KEY (`predecessor_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedules_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `schedules` (`id`, `project_id`, `master_activity_id`, `predecessor_id`, `name`, `duration_days`, `baseline_start_date`, `baseline_end_date`, `current_start_date`, `current_end_date`, `feasibility`, `has_weather_alert`, `weather_rule_text`, `status`, `continued_same_day`, `resume_time`, `mitigation_notes`, `created_at`, `updated_at`) VALUES ('1', '1', NULL, NULL, 'buhos', '5', '2026-10-01', '2026-10-05', '2026-10-04', '2026-10-08', 'Feasible', '0', 'Rain < 50%, Wind < 40km/h', 'In Progress', '1', '14:00', 'On-site mitigation applied (protective covers / rapid-cure additive)', '2026-10-04 10:00:20', '2026-10-05 08:59:45');
INSERT INTO `schedules` (`id`, `project_id`, `master_activity_id`, `predecessor_id`, `name`, `duration_days`, `baseline_start_date`, `baseline_end_date`, `current_start_date`, `current_end_date`, `feasibility`, `has_weather_alert`, `weather_rule_text`, `status`, `continued_same_day`, `resume_time`, `mitigation_notes`, `created_at`, `updated_at`) VALUES ('6', '1', NULL, NULL, 'tryyy', '10', '2026-10-08', '2026-10-17', '2026-10-08', '2026-10-17', 'Feasible', '0', 'Rain < 40%, Wind < 40km/h', 'Scheduled', '0', NULL, NULL, '2026-10-04 10:28:00', '2026-10-04 10:28:00');

DROP TABLE IF EXISTS `weather_thresholds`;
CREATE TABLE `weather_thresholds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` bigint unsigned DEFAULT NULL,
  `master_activity_id` bigint unsigned DEFAULT NULL,
  `max_rain_probability` decimal(5,2) NOT NULL DEFAULT '40.00',
  `max_rain_volume_mm` decimal(5,2) NOT NULL DEFAULT '2.50',
  `max_wind_speed_kmh` decimal(5,2) NOT NULL DEFAULT '40.00',
  `max_temperature_c` decimal(5,2) NOT NULL DEFAULT '36.00',
  `worker_safety_trigger` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Rainfall > 3mm/hr',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `weather_thresholds_schedule_id_foreign` (`schedule_id`),
  KEY `weather_thresholds_master_activity_id_foreign` (`master_activity_id`),
  CONSTRAINT `weather_thresholds_master_activity_id_foreign` FOREIGN KEY (`master_activity_id`) REFERENCES `master_activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `weather_thresholds_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `progress_records`;
CREATE TABLE `progress_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `record_date` date NOT NULL,
  `planned_progress` int NOT NULL DEFAULT '0',
  `actual_progress` int NOT NULL DEFAULT '0',
  `variance` int NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'On Track',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `progress_records_schedule_id_foreign` (`schedule_id`),
  KEY `progress_records_user_id_foreign` (`user_id`),
  CONSTRAINT `progress_records_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `progress_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `progress_records` (`id`, `schedule_id`, `user_id`, `record_date`, `planned_progress`, `actual_progress`, `variance`, `status`, `notes`, `created_at`, `updated_at`) VALUES ('1', '1', '1', '2026-10-05', '50', '20', '-30', 'Critical Delay', 'try kung lalabas sa audit', '2026-10-05 07:35:57', '2026-10-05 07:35:57');
INSERT INTO `progress_records` (`id`, `schedule_id`, `user_id`, `record_date`, `planned_progress`, `actual_progress`, `variance`, `status`, `notes`, `created_at`, `updated_at`) VALUES ('2', '6', '1', '2026-10-05', '50', '0', '-50', 'Critical Delay', 'try kung lalabas sa audit', '2026-10-05 07:35:57', '2026-10-05 07:35:57');

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `project_id` bigint unsigned DEFAULT NULL,
  `schedule_id` bigint unsigned DEFAULT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'System',
  `user_role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Engineer',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Weather Decision',
  `action_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_activity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `weather_snapshot` json DEFAULT NULL,
  `outcome_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Continue Same Day',
  `outcome_details` text COLLATE utf8mb4_unicode_ci,
  `response_time_minutes` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_company_id_foreign` (`company_id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_project_id_foreign` (`project_id`),
  KEY `audit_logs_schedule_id_foreign` (`schedule_id`),
  CONSTRAINT `audit_logs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('1', '2', '2', NULL, NULL, 'Engr. Juan Dela Cruz', 'project_engineer', 'System Admin', 'Contractor Organization Registered', 'Company: try contractor (ASFB)', NULL, 'Company Registered', 'Registered contractor try contractor with code ASFB', '0', '2026-10-04 09:56:00', '2026-10-04 09:56:00');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('2', '1', '2', '1', NULL, 'System', 'Engineer', 'Schedule Change', 'Project Workspace Created', 'try project', NULL, 'Schedule Approved', 'Registered project workspace try project (CLSU-BLDG-2026-0101)', '0', '2026-10-04 09:58:31', '2026-10-04 09:58:31');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('3', '1', '2', NULL, NULL, 'System', 'Engineer', 'System Admin', 'New Master Activity Registered', 'Catalog: buhos', NULL, 'Activity Created', 'Added buhos (Structural) to library', '0', '2026-10-04 09:59:46', '2026-10-04 09:59:46');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('4', '1', '2', NULL, NULL, 'System', 'Engineer', 'System Admin', 'New Master Activity Registered', 'Catalog: try 2', NULL, 'Activity Created', 'Added try 2 (Structural) to library', '0', '2026-10-04 10:15:58', '2026-10-04 10:15:58');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('5', '1', '2', NULL, NULL, 'System', 'Engineer', 'System Admin', 'New Master Activity Registered', 'Catalog: try', NULL, 'Activity Created', 'Added try (Structural) to library', '0', '2026-10-04 10:25:08', '2026-10-04 10:25:08');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('6', '2', '1', '2', NULL, 'System', 'Engineer', 'Schedule Change', 'Project Workspace Created', 'try 23', NULL, 'Schedule Approved', 'Registered project workspace try 23 (CLSU-BLDG-2026-02)', '0', '2026-10-05 01:37:24', '2026-10-05 01:37:24');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('7', '1', '1', '1', NULL, 'admin', 'Project Engineer', 'Progress Update', 'Daily Progress Accomplishment Submitted', 'buhos', NULL, 'Progress Saved', 'Updated actual progress to 20% (Variance: -30%)', '0', '2026-10-05 07:35:57', '2026-10-05 07:35:57');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('8', '1', '1', '1', NULL, 'admin', 'Project Engineer', 'Progress Update', 'Daily Progress Accomplishment Submitted', 'tryyy', NULL, 'Progress Saved', 'Updated actual progress to 0% (Variance: -50%)', '0', '2026-10-05 07:35:57', '2026-10-05 07:35:57');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('9', '1', '1', '1', '1', 'admin', 'admin', 'Weather Decision', 'Rescheduled After Alert', 'buhos', '{\"wind\": \"22 km/h\", \"rainProb\": 75, \"feasibility\": \"Conditionally Feasible\"}', 'Reschedule', 'Shifted Oct 01 → Oct 04. (0 Successors Adjusted)', '12', '2026-10-05 08:17:29', '2026-10-05 08:17:29');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('10', '1', '1', '1', '1', 'admin', 'admin', 'Weather Decision', 'Continued Same Day', 'buhos', '{\"mitigation\": \"Activity on-site mitigation applied with resume target at 13:00\", \"resume_time\": \"13:00\"}', 'Continue Same Day', 'Mitigation confirmed: Activity on-site mitigation applied with resume target at 13:00. Work resuming at 13:00.', '5', '2026-10-05 08:41:19', '2026-10-05 08:41:19');
INSERT INTO `audit_logs` (`id`, `company_id`, `user_id`, `project_id`, `schedule_id`, `user_name`, `user_role`, `category`, `action_title`, `target_activity`, `weather_snapshot`, `outcome_type`, `outcome_details`, `response_time_minutes`, `created_at`, `updated_at`) VALUES ('11', '1', '1', '1', '1', 'admin', 'admin', 'Weather Decision', 'Continued Same Day', 'buhos', '{\"mitigation\": \"On-site mitigation applied (protective covers / rapid-cure additive)\", \"resume_time\": \"14:00\"}', 'Continue Same Day', 'Mitigation confirmed: On-site mitigation applied (protective covers / rapid-cure additive). Work resuming at 14:00.', '5', '2026-10-05 08:59:45', '2026-10-05 08:59:45');

SET FOREIGN_KEY_CHECKS=1;
