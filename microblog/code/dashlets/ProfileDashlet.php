<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class ProfileDashlet extends Dashlet {
	public static $title = 'Profile';
}

class ProfileDashlet_Controller extends Dashlet_Controller {
	
	static $permission_options = array(
		'Hidden',
		'Friends only',
		'Public'
	);
	
	public function SettingsForm() {
		$fields = new FieldList();

		$opts = array_combine(self::$permission_options, self::$permission_options);
		$fields->push(new DropdownField('DefaultPostPermission', _t('ProfileDashlet.NEW_POST_PERM', 'New post settings'), $opts));
		
		$opts = array_merge(array('' => ''), $opts);
		$fields->push(new DropdownField('SetPermissions', _t('ProfileDashlet.POST_PERM', 'Set all posts to'), $opts));
		
		$actions = new FieldList(new FormAction('savesettings', _t('ProfileDashlet.SAVE', 'Update')));
		
		$form = new Form($this, 'SettingsForm', $fields, $actions);
		$form->addExtraClass('ajaxsubmitted');
		
		$form->loadDataFrom(Member::currentUser());
		return $form;
	}
	
	public function savesettings($data, Form $form) {
		$fields = array(
			'DefaultPostPermission'
		);
		
		$form->saveInto(Member::currentUser(), $fields);
		Member::currentUser()->write();
		
		return $this->SettingsForm()->forAjaxTemplate();
	}
}