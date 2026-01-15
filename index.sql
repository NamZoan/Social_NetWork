CREATE TABLE `user_interactions` (

  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,

  `user_id` bigint(20) UNSIGNED NOT NULL,

  `post_id` bigint(20) UNSIGNED NOT NULL,

  `interaction_type` enum('view', 'click', 'like', 'share', 'comment', 'skip', 'profile_visit') NOT NULL,

  PRIMARY KEY (`id`),

  KEY `idx_user_post` (`user_id`, `post_id`),

  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,

  FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE

) ENGINE=InnoDB;