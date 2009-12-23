/*
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright &copy; 2009 Pogostick, LLC
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * DDL to create a standard code table for use with CPSCodeTableModel.
 * Adjust as necessary.
 *
 * @package 	psYiiExtensions.templates
 * @subpackage 	ddl
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id$
 * @since 		v1.0.6
 *  
 * @filesource
 * 
 */

create table %%TABLE_NAME%% {
  `id` int(19) NOT NULL,
  `code_type_text` varchar(60) NOT NULL,                               
  `code_abbr_text` varchar(60) NOT NULL,                               
  `code_desc_text` varchar(255) NOT NULL,                              
  `parnt_code_id` int(19) DEFAULT NULL,                                
  `assoc_value_nbr` double DEFAULT NULL,                               
  `assoc_text` varchar(255) DEFAULT NULL,                              
  `create_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',       
  `lmod_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,            
  PRIMARY KEY (`id`),                                                  
  UNIQUE KEY `pk_code_id` (`id`),                                      
  UNIQUE KEY `ixu_code_code_type_abbr` (`code_type_text`,`code_abbr_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8                                   
