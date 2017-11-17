<?php
return array (
  'Account' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Account\\MailChimp',
    ),
    'beforeRemove' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Account\\MailChimp',
    ),
  ),
  'Call' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Call\\Google',
    ),
  ),
  'Campaign' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Campaign\\MailChimp',
    ),
  ),
  'Common' => 
  array (
    'afterSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Common\\Workflow',
      1 => '\\Espo\\Hooks\\Common\\AssignmentEmailNotification',
      2 => '\\Espo\\Hooks\\Common\\Stream',
      3 => '\\Espo\\Hooks\\Common\\Notifications',
    ),
    'beforeSave' => 
    array (
      0 => '\\Espo\\Hooks\\Common\\CurrencyConverted',
      1 => '\\Espo\\Hooks\\Common\\Formula',
      2 => '\\Espo\\Hooks\\Common\\NextNumber',
    ),
    'beforeRemove' => 
    array (
      0 => '\\Espo\\Hooks\\Common\\Notifications',
    ),
    'afterRemove' => 
    array (
      0 => '\\Espo\\Hooks\\Common\\Stream',
      1 => '\\Espo\\Hooks\\Common\\Notifications',
    ),
    'afterRelate' => 
    array (
      0 => '\\Espo\\Hooks\\Common\\Stream',
    ),
    'afterUnrelate' => 
    array (
      0 => '\\Espo\\Hooks\\Common\\Stream',
    ),
  ),
  'Contact' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Contact\\MailChimp',
    ),
    'beforeRemove' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Contact\\MailChimp',
    ),
  ),
  'ExternalAccount' => 
  array (
    'afterSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\ExternalAccount\\Google',
    ),
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\ExternalAccount\\Google',
    ),
  ),
  'Integration' => 
  array (
    'afterSave' => 
    array (
      0 => '\\Espo\\Hooks\\Integration\\GoogleMaps',
      1 => '\\Espo\\Modules\\Advanced\\Hooks\\Integration\\MailChimp',
    ),
  ),
  'Lead' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Lead\\MailChimp',
    ),
    'beforeRemove' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Lead\\MailChimp',
    ),
  ),
  'Meeting' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Meeting\\Google',
    ),
  ),
  'Opportunity' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Opportunity\\OpportunityItem',
    ),
    'afterSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Opportunity\\OpportunityItem',
    ),
  ),
  'Quote' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Quote\\QuoteItem',
    ),
    'afterSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Quote\\QuoteItem',
    ),
  ),
  'TargetList' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\TargetList\\MailChimp',
    ),
  ),
  'User' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\User\\MailChimp',
    ),
    'beforeRemove' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\User\\MailChimp',
    ),
  ),
  'Workflow' => 
  array (
    'afterSave' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Workflow\\ReloadWorkflows',
    ),
    'afterRemove' => 
    array (
      0 => '\\Espo\\Modules\\Advanced\\Hooks\\Workflow\\ReloadWorkflows',
    ),
  ),
  'Note' => 
  array (
    'beforeSave' => 
    array (
      0 => '\\Espo\\Hooks\\Note\\Mentions',
    ),
    'afterSave' => 
    array (
      0 => '\\Espo\\Hooks\\Note\\Notifications',
    ),
  ),
);
?>