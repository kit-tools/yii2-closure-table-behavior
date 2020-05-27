SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- База данных: `yii2_closure_table_behavior`
--
CREATE DATABASE IF NOT EXISTS `yii2_closure_table_behavior` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `yii2_closure_table_behavior`;

DROP TABLE IF EXISTS `menu`;
DROP TABLE IF EXISTS `menu_tree_path`;
-- --------------------------------------------------------

--
-- Структура таблицы `menu`
--

CREATE TABLE `menu` (
  `id` int(11) UNSIGNED NOT NULL,
  `parent_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID parent menu',
  `title` varchar(30) NOT NULL COMMENT 'Menu title'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `menu_tree_path`
--

CREATE TABLE `menu_tree_path` (
  `parent_id` int(11) UNSIGNED NOT NULL COMMENT 'ID parent',
  `child_id` int(11) UNSIGNED NOT NULL COMMENT 'ID child',
  `nearest_parent_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID nearest parent',
  `parent_level` int(11) UNSIGNED DEFAULT NULL COMMENT 'Parent level',
  `child_level` int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Child level',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created date'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_menu_parent_id` (`parent_id`);

--
-- Индексы таблицы `menu_tree_path`
--
ALTER TABLE `menu_tree_path`
  ADD PRIMARY KEY (`parent_id`,`child_id`),
  ADD KEY `fk_menu_tree_path_child` (`child_id`),
  ADD KEY `fk_menu_tree_path_nearest_parent` (`nearest_parent_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `fk_menu_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `menu` (`id`);

--
-- Ограничения внешнего ключа таблицы `menu_tree_path`
--
ALTER TABLE `menu_tree_path`
  ADD CONSTRAINT `fk_menu_tree_path_child` FOREIGN KEY (`child_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_menu_tree_path_nearest_parent` FOREIGN KEY (`nearest_parent_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_menu_tree_path_parent` FOREIGN KEY (`parent_id`) REFERENCES `menu` (`id`) ON DELETE CASCADE;
COMMIT;
