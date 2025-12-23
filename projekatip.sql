-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 01:18 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projekatip`
--

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `online_url` varchar(255) DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `organizer_id` int(11) NOT NULL,
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `location`, `online_url`, `starts_at`, `ends_at`, `capacity`, `game_id`, `organizer_id`, `is_online`, `created_at`) VALUES
(1, 'Igranje katana', 'Zabava!!!!', 'kod mene', NULL, '2025-10-22 12:00:00', '2025-10-22 18:00:00', 10, 1, 3, 0, '2025-10-02 21:51:07'),
(2, 'Tablić u grupama po 4', 'Igramo ceo dan, donesite sokove!!!', 'Nemanjina 28', NULL, '2025-10-16 13:00:00', '2025-10-16 23:00:00', 7, 3, 1, 0, '2025-10-03 16:23:22'),
(3, 'Igranje Una', 'Igramo Uno, ponesite slatkiše i dobro raspoloženje. Samo bez nervoze', 'Obilićeva 12', NULL, '2025-10-18 12:00:00', '2025-10-18 17:00:00', 4, 19, 2, 0, '2025-10-14 01:19:54');

-- --------------------------------------------------------

--
-- Table structure for table `event_attendees`
--

CREATE TABLE `event_attendees` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `checked_in_at` datetime DEFAULT NULL,
  `status` enum('going','') NOT NULL DEFAULT 'going'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_attendees`
--

INSERT INTO `event_attendees` (`id`, `event_id`, `user_id`, `checked_in_at`, `status`) VALUES
(1, 1, 2, '2025-10-03 00:36:13', 'going'),
(2, 2, 3, '2025-10-03 19:11:18', 'going'),
(16, 2, 2, '2025-10-03 19:28:57', 'going'),
(17, 3, 1, '2025-10-14 14:45:29', 'going');

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `min_players` int(11) NOT NULL,
  `max_players` int(11) NOT NULL,
  `play_time` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `title`, `description`, `image_path`, `min_players`, `max_players`, `play_time`, `year`, `created_at`) VALUES
(1, 'Catan', 'Catan je društvena strateška igra objavljena 1995. godine. \r\nIgrači kolonizuju ostrvo Katan, grade puteve i naselja, \r\nrazmenjuju resurse (drvo, cigla, žito, ovce, ruda) i \r\ntakmiče se da prvi dostignu 10 poena. \r\nIgra je poznata po dinamičnoj trgovini i raznim strategijama.', 'https://images.theconversation.com/files/567624/original/file-20240102-19-2tzi0o.jpg?ixlib=rb-4.1.0&rect=30%2C15%2C5106%2C3404&q=20&auto=format&w=768&h=512&fit=crop&dpr=2&usm=12&cs=strip', 3, 4, 60, 10, '2025-09-30 20:45:45'),
(2, 'Twister', 'Twister je fizička društvena igra iz 1966. godine. \r\nIgrači stoje na prostirci sa obojenim krugovima \r\n(crvena, plava, žuta, zelena), dok moderator okreće rulet \r\nkoji određuje na koju boju i deo tela (ruka/noga) igrač mora da stane. \r\nCilj je ostati u ravnoteži dok se igrači zapliću jedni s drugima.', 'uploads/twister.jpg', 2, 4, 10, 6, '2025-10-02 19:53:23'),
(3, 'Tablić', 'Tablić je veoma popularna kartaška igra na Balkanu. Najviše se igra u Srbiji, Crnoj Gori, Bosni i Hercegovini i Makedoniji. Obično se igra u dvoje ili četvoro. Kada igraju četiri igrača, onda se igra u parovima i parovi sede dijagonalno jedan od drugog. Igra se sa jednim špilom od 52 karte. Može takođe da se igra i u troje, s tim što svaki igrač igra za sebe, a u zadnjem deljenju se po 4 karte.', 'https://vreme.com/wp-content/uploads/2021/11/860015_uzinanje.jpg', 2, 4, 20, 7, '2025-10-02 18:05:44'),
(14, 'Ne ljuti se čoveče', 'Klasična porodična igra u kojoj igrači bacaju kocku i pomeraju figure do cilja — ali pazite, drugi vas mogu vratiti na početak!', 'https://delfi.rs/_img/artikli/2022/03/drustvena_igra_-_ne_ljuti_se_covece_vv.jpg', 2, 4, 30, 6, '2025-10-14 00:52:26'),
(15, 'Jamb', 'Popularna igra sa kockicama u kojoj se pokušava ostvariti što više kombinacija i bodova kroz 13 kolona i redova.', 'https://www.nird.hr/images/thumbs/0000143_591010_510.jpeg', 1, 6, 40, 8, '2025-10-14 00:52:26'),
(16, 'Monopol', 'Igra kupovine i trgovanja nekretninama u kojoj igrači pokušavaju da bankrotiraju protivnike i postanu najbogatiji.', 'https://www.oddoigracke.rs/proizvodi/6247/DRUSTVENA-IGRA-MONOPOL-b.jpg', 2, 6, 90, 8, '2025-10-14 00:52:26'),
(17, 'Riziko', 'Strateška igra osvajanja sveta. Igrači raspoređuju vojske, napadaju teritorije i prave saveze da bi dominirali mapom.', 'https://www.knjizare-vulkan.rs/files/watermark/files/images/slike_proizvoda/thumbs_w/382768_2_w_1200_1200px.jpg', 2, 6, 120, 10, '2025-10-14 00:52:26'),
(18, 'Sumnjivo lice', 'Zabavna detektivska igra u kojoj igrači pokušavaju da pogode identitet sumnjivog na osnovu tragova i pitanja.', 'https://m.media-amazon.com/images/I/81M8EafOQoL._UF894,1000_QL80_.jpg', 3, 6, 25, 7, '2025-10-14 00:52:26'),
(19, 'Uno', 'Brza kartaška igra u kojoj pokušavate da se oslobodite svih svojih karata – ali pazite na posebne karte i boje!', 'https://brewquets.com.au/cdn/shop/files/UnoCards_2000x.jpg?v=1686630700', 2, 10, 20, 7, '2025-10-14 00:52:26'),
(20, 'Čoveče, popij!', 'Zabavna verzija klasične igre „Ne ljuti se čoveče“, sa zadacima za odrasle — smeh zagarantovan!', 'https://hocuto.hr/wp-content/uploads/2022/10/covjece.jpg', 2, 6, 25, 18, '2025-10-14 00:52:26'),
(21, 'Alias', 'Timovi pokušavaju da objasne pojmove bez korišćenja te reči. Brza, zabavna i glasna igra za svako društvo.', 'https://www.dantkom.hr/wp-content/uploads/2024/06/Drustvena-igra-Alias-Original-Tactic-1.jpg', 4, 10, 45, 10, '2025-10-14 00:52:26'),
(22, 'Pictionary', 'Igra crtanja i pogađanja pojmova. Igrači crtaju dok drugi pokušavaju da pogode šta je prikazano.', 'https://shop.mattel.com.au/cdn/shop/files/JDX96_eRetail_Assets__without_Feature_Callouts__1.jpg?v=1737342251&width=1100', 3, 8, 60, 8, '2025-10-14 00:52:26'),
(23, 'Scrabble', 'Klasična igra slaganja reči. Igrači formiraju reči na tabli i osvajaju poene na osnovu slova i pozicija.', 'https://upload.wikimedia.org/wikipedia/commons/5/5d/Scrabble_game_in_progress.jpg', 2, 4, 50, 10, '2025-10-14 00:52:26');

-- --------------------------------------------------------

--
-- Table structure for table `game_genre`
--

CREATE TABLE `game_genre` (
  `game_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_genre`
--

INSERT INTO `game_genre` (`game_id`, `genre_id`) VALUES
(1, 1),
(2, 5),
(3, 2),
(14, 4),
(14, 7),
(15, 3),
(15, 7),
(16, 4),
(16, 6),
(16, 7),
(17, 1),
(18, 4),
(18, 6),
(19, 2),
(19, 4),
(20, 6),
(21, 6),
(21, 8),
(22, 4),
(23, 3),
(23, 4);

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `name` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `name`) VALUES
(8, 'Asocijacije'),
(5, 'Fizička igra'),
(2, 'Igra karata'),
(7, 'Kockice'),
(3, 'Mozgalica'),
(6, 'Party'),
(4, 'Porodična igra'),
(1, 'Strategija');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 10),
  `comment` text DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `game_id`, `rating`, `comment`, `is_deleted`, `created_at`) VALUES
(1, 1, 1, 5, 'Odlična društvena igra za ekipu! Pravila su jednostavna, a trgovina i pregovori čine svaku partiju drugačijom i zabavnom.', 0, '2025-10-01 20:18:01'),
(4, 3, 1, 4, 'Super zabavno, ali uvek gubim :(', 0, '2025-10-02 17:20:01'),
(6, 1, 14, 4, 'Zabava za celu porodicu, deca je posebno vole!', 0, '2025-10-14 01:11:26'),
(7, 1, 20, 2, 'Ne sećam se kad sam igrao', 0, '2025-10-14 01:12:04'),
(8, 1, 16, 5, 'Najbolja!', 0, '2025-10-14 01:12:26'),
(9, 1, 19, 5, 'Obožavam da stavim 4+ na 4+!', 0, '2025-10-14 01:13:32'),
(10, 1, 15, 2, 'Dosadna', 0, '2025-10-14 01:14:05'),
(11, 1, 2, 1, 'Dobra ako želite da slomite nogu', 0, '2025-10-14 01:15:03'),
(12, 2, 23, 3, 'Klasika', 0, '2025-10-14 01:15:37'),
(13, 2, 18, 5, 'Super za decu', 0, '2025-10-14 01:16:16'),
(14, 1, 3, 5, 'Volim!', 0, '2025-10-14 01:20:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` varchar(20) NOT NULL,
  `role` enum('user','admin','','') NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'korisnik1', 'korisnik@gmail.com', 'Korisnik123.', 'user', '2025-09-30 20:42:23'),
(2, 'korisnik2', 'korisnik2@gmail.com', 'Korisnik123.', 'user', '2025-09-30 20:48:43'),
(3, 'Admin1', 'admin@gmail.com', 'Admin123.', 'admin', '2025-10-01 18:41:29');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`user_id`, `game_id`) VALUES
(1, 17),
(1, 21),
(2, 22),
(3, 18),
(3, 20),
(3, 21),
(3, 23);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_events_game` (`game_id`),
  ADD KEY `fk_events_user` (`organizer_id`);

--
-- Indexes for table `event_attendees`
--
ALTER TABLE `event_attendees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_event_user` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `game_genre`
--
ALTER TABLE `game_genre`
  ADD PRIMARY KEY (`game_id`,`genre_id`),
  ADD KEY `genre_id` (`genre_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `event_attendees`
--
ALTER TABLE `event_attendees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_game` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_events_user` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_attendees`
--
ALTER TABLE `event_attendees`
  ADD CONSTRAINT `event_attendees_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_attendees_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `game_genre`
--
ALTER TABLE `game_genre`
  ADD CONSTRAINT `game_genre_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_genre_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
