CREATE TABLE `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `clinic` varchar(100) NOT NULL,
  `number` int(11) NOT NULL,
  `status` enum('waiting','called','announced','completed') NOT NULL DEFAULT 'waiting',
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_user_queue` (`user_id`),
  CONSTRAINT `fk_user_queue` FOREIGN KEY (`user_id`) REFERENCES `queue_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `queue_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','counter','cashier') NOT NULL DEFAULT 'counter',
  `window_number` tinyint(3) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_screen` int(11) DEFAULT NULL COMMENT 'الشاشة/الشباك المربوطة بالمستخدم',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `assigned_screen` (`assigned_screen`),
  CONSTRAINT `queue_users_ibfk_1` FOREIGN KEY (`assigned_screen`) REFERENCES `screens` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `screens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `screen_number` tinyint(3) unsigned NOT NULL COMMENT 'رقم الشاشة (1،2،3...)',
  `ip` varchar(45) NOT NULL COMMENT 'IP الخاص بالشاشة',
  `port` smallint(5) unsigned NOT NULL COMMENT 'المنفذ (port)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `screen_number` (`screen_number`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_clinics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `clinic` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_clinics_user` (`user_id`),
  CONSTRAINT `fk_user_clinics_user` FOREIGN KEY (`user_id`) REFERENCES `queue_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
