<?php
return array(
	'class'=>'model',
	'orm'=>array(
		'table'=>'class',
		'keys'=>array( 'extension','namespace','name','version' ),
		'hasMany:methods'=>array(
			'fromkeys'=>array( 'extension','namespace','name','version' ),
			'tokeys'=>array( 'extension','namespace','name','version' ),
			'table'=>'method',
			'keys'=>array('extension', 'namespace','class','name','version'),
			'orm'=>array(
				'hasMany:parameters'=>array(
					'fromkeys'=>array('version','name','class','namespace'),
					'tokeys'=>array('version','method','class','namespace'),
					'table'=>'parameter',
					'orm'=>array(
						'keys'=>array('extension', 'namespace','class','method','name','version'),
					)
				)
			)
		)
	)
);