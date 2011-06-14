<?php
/**
 * This file is part of the psYiiExtensions package.
 * 
 * @copyright Copyright (c) 2009-2011 Pogostick, LLC.
 * @link http://www.pogostick.com Pogostick, LLC.
 * @license http://www.pogostick.com/licensing
 */

/**
 * This is the template for generating the admin view for crud.
 * The following variables are available in this template:
 * - $ID: the primary key name
 * - $modelClass: the model class name
 * - $columns: a list of column schema objects
 * 
 * @package 	psYiiExtensions.templates
 * @subpackage 	crud
 * 
 * @author 		Jerry Ablan <jablan@pogostick.com>
 * @version 	SVN: $Id: admin.php 380 2010-04-05 11:20:21Z jerryablan@gmail.com $
 * @since 		v1.0.6
 *  
 * @filesource
 */

//	Build an array for the data grid...
$_sCols = null;
foreach ( $columns as $column ) $_sCols .= ( ( $_sCols ) ? ', ' : '' ) . '\'' . $column->name . '\'';
$_sCols = "array( {$_sCols } )";
	
//	Include our header...
$className = 'admin view';
include( Yii::getPathOfAlias( 'pogostick.templates.crud' ) . '/build_template_header.php' );

//	And output the rest...
echo <<<HTML
	echo CPSDataGrid::createEx( $models, 
		array( 
			'formHeader' => array( 'title' => '{$modelClass} Manager', 'menuButtons' => array( 'new' ), 'itemName' => '{$modelClass}', ),
			'dataItem' => 'setting', 
			'columns' => {$_sCols},
			'actions' => array( 'edit', 'delete' ), 
			'sort' => $sort,
			'pages' => $pages,
			'pagerOptions' => array( 'header' => '' ),
			'linkView' => 'update',
		)
	);
HTML;
