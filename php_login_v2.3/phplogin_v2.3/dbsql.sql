CREATE TABLE `usuarios` (
  `id` bigint(20) NOT NULL auto_increment,
  `md5_id` varchar(200) collate latin1_general_ci NOT NULL default '',
  `nombre` tinytext collate latin1_general_ci NOT NULL,
  `nombre_usuario` varchar(200) collate latin1_general_ci NOT NULL default '',
  `pwd` varchar(220) collate latin1_general_ci NOT NULL default '',
  `email` varchar(220) collate latin1_general_ci NOT NULL default '',
  `tipo_usuario` tinyint(4) NOT NULL default '0',
  `fecha` date NOT NULL default '0000-00-00',
  `notas` varchar(220) collate latin1_general_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`),
  FULLTEXT KEY `idx_search` (`nombre`,`email`,`nombre_usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=55 ;


INSERT INTO `usuarios` VALUES (54, '', 'admin', 'admin',  'admin', 'admin@localhost', '1', 0x323031302d30352d3034, 'First admin');
        