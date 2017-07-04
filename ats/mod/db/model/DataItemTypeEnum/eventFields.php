<?php

$eventFields = array(
		'id'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('id'),
				'desc'=>'Id zdarzenia',
				'dbColumn'=>'eventId',
				'sqlColumn'=>'calendar_event_id',
		),
		'type'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('select'),
				'desc'=>'Typ zdarzenia',
				'dbColumn'=>'type',
				'sqlColumn'=>'type',
				'options'=>'eventType',
		),
		'startDateTime'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('dateTime'),
				'desc'=>'Data i czas zdarzenia',
				'dbColumn'=>'date',
				'sqlColumn'=>'date',
		),
		'subject'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Temat',
				'dbColumn'=>'title',
				'sqlColumn'=>'title',
		),
		'allDay'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'desc'=>'Cały dzień',
				'dbColumn'=>'allDay',
				'sqlColumn'=>'all_day',
		),
		'dataItemId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id elementu',
				'dbColumn'=>'dataItemId',
				'sqlColumn'=>'data_item_id',
		),
		'dataItemType'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('select'),
				'desc'=>'Typ elementu',
				'dbColumn'=>'dataItemType',
				'sqlColumn'=>'data_item_type',
				'options'=>'dataItemType'//E::enum('dataItemType')->getAsoc('dbValue','desc'),//dataItemType.lazyinit!!!
		),
		'userId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Wprowadził',
				'dbColumn'=>'enteredBy',
				'sqlColumn'=>'entered_by',
		),
		'dtCreated'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('date'),
				'desc'=>'Data utworzenia',
				'dbColumn'=>'dateCreated',
				'sqlColumn'=>'date_created',
		),
		'dtModified'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('date'),
				'desc'=>'Data modyfikacji',
				'dbColumn'=>'dateModified',
				'sqlColumn'=>'date_modified',
		),
		'siteId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>__('Site id'),
				'dbColumn'=>'siteId',
				'sqlColumn'=>'site_id',
		),
		'jobOrderId'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('fk'),
				'desc'=>'Id zlecenia',
				'dbColumn'=>'jobOrderID',
				'sqlColumn'=>'joborder_id',
		),
		'description'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'dbColumn'=>'description',
		),
		'length'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'dbColumn'=>'length',
				'sqlColumn'=>'duration',				
		),
		'reminderEnabled'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'dbColumn'=>'reminderEnabled',
				'sqlColumn'=>'reminder_enabled',
		),
		'reminderEmail'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'dbColumn'=>'reminderEmail',
				'sqlColumn'=>'reminder_email',
		),
		'reminderTime'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'dbColumn'=>'reminderTime',
				'sqlColumn'=>'reminder_time',
		),
		'public'=>array(
				'fieldTypeEnum'=>E::dataItemFieldType('string'),
				'dbColumn'=>'public',
				'sqlColumn'=>'public',
		),
	);
