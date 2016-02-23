<?php
/**
 * @package      Virtual Currency
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2016 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * VirtualCurrency Account controller class.
 *
 * @package        VirtualCurrency
 * @subpackage     Components
 * @since          1.6
 */
class VirtualCurrencyControllerAccount extends Prism\Controller\Form\Backend
{
    public function save($key = null, $urlVar = null)
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $data   = $this->input->post->get('jform', array(), 'array');
        $itemId = JArrayHelper::getValue($data, 'id');

        // Prepare return data
        $redirectOptions = array(
            'task' => $this->getTask(),
            'id'   => $itemId
        );

        $model = $this->getModel();
        /** @var $model VirtualCurrencyModelAccount */

        $form = $model->getForm($data, false);
        /** @var $form JForm */

        if (!$form) {
            throw new Exception(JText::_('COM_VIRTUALCURRENCY_ERROR_FORM_CANNOT_BE_LOADED'), 500);
        }

        // Validate the form
        $validData = $model->validate($form, $data);

        // Check for errors.
        if ($validData === false) {
            $this->displayNotice($form->getErrors(), $redirectOptions);

            return;
        }

        // Check user ID
        $userId = JArrayHelper::getValue($validData, 'user_id');
        if (!$userId) {
            $this->displayNotice(JText::_('COM_VIRTUALCURRENCY_ERROR_INVALID_USER'), $redirectOptions);

            return;
        }

        // Check for existing account for that currency
        $currencyId = JArrayHelper::getValue($data, 'currency_id');
        if (!$itemId and $model->isExist($userId, $currencyId)) {
            $this->displayNotice(JText::_('COM_VIRTUALCURRENCY_ERROR_ACCOUNT_EXISTS'), $redirectOptions);

            return;
        }

        try {

            $itemId = $model->save($validData);

            // Prepare return data
            $redirectOptions['id'] = $itemId;

        } catch (Exception $e) {
            JLog::add($e->getMessage());
            throw new Exception(JText::_('COM_VIRTUALCURRENCY_ERROR_SYSTEM'));
        }

        $this->displayMessage(JText::_('COM_VIRTUALCURRENCY_ACCOUNT_SAVED'), $redirectOptions);

    }
}
