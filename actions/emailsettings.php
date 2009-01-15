<?php
/*
 * Laconica - a distributed open-source microblogging tool
 * Copyright (C) 2008, Controlez-Vous, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('LACONICA')) { exit(1); }

require_once(INSTALLDIR.'/lib/settingsaction.php');

class EmailsettingsAction extends SettingsAction
{

    function get_instructions()
    {
        return _('Manage how you get email from %%site.name%%.');
    }

    function show_form($msg=null, $success=false)
    {
        $user = common_current_user();
        $this->form_header(_('Email Settings'), $msg, $success);
        $this->elementStart('form', array('method' => 'post',
                                           'id' => 'emailsettings',
                                           'action' =>
                                           common_local_url('emailsettings')));
        $this->hidden('token', common_session_token());

        $this->element('h2', null, _('Address'));

        if ($user->email) {
            $this->elementStart('p');
            $this->element('span', 'address confirmed', $user->email);
            $this->element('span', 'input_instructions',
                           _('Current confirmed email address.'));
            $this->hidden('email', $user->email);
            $this->elementEnd('p');
            $this->submit('remove', _('Remove'));
        } else {
            $confirm = $this->get_confirmation();
            if ($confirm) {
                $this->elementStart('p');
                $this->element('span', 'address unconfirmed', $confirm->address);
                $this->element('span', 'input_instructions',
                               _('Awaiting confirmation on this address. Check your inbox (and spam box!) for a message with further instructions.'));
                $this->hidden('email', $confirm->address);
                $this->elementEnd('p');
                $this->submit('cancel', _('Cancel'));
            } else {
                $this->input('email', _('Email Address'),
                             ($this->arg('email')) ? $this->arg('email') : null,
                             _('Email address, like "UserName@example.org"'));
                $this->submit('add', _('Add'));
            }
        }

        if ($user->email) {
            $this->element('h2', null, _('Incoming email'));
            
            if ($user->incomingemail) {
                $this->elementStart('p');
                $this->element('span', 'address', $user->incomingemail);
                $this->element('span', 'input_instructions',
                               _('Send email to this address to post new notices.'));
                $this->elementEnd('p');
                $this->submit('removeincoming', _('Remove'));
            }
            
            $this->elementStart('p');
            $this->element('span', 'input_instructions',
                           _('Make a new email address for posting to; cancels the old one.'));
            $this->elementEnd('p');
            $this->submit('newincoming', _('New'));
        }
        
        $this->element('h2', null, _('Preferences'));

        $this->checkbox('emailnotifysub',
                        _('Send me notices of new subscriptions through email.'),
                        $user->emailnotifysub);
        $this->checkbox('emailnotifyfav',
                        _('Send me email when someone adds my notice as a favorite.'),
                        $user->emailnotifyfav);
        $this->checkbox('emailnotifymsg',
                        _('Send me email when someone sends me a private message.'),
                        $user->emailnotifymsg);
        $this->checkbox('emailnotifynudge',
                        _('Allow friends to nudge me and send me an email.'),
                        $user->emailnotifynudge);
        $this->checkbox('emailpost',
                        _('I want to post notices by email.'),
                        $user->emailpost);
        $this->checkbox('emailmicroid',
                        _('Publish a MicroID for my email address.'),
                        $user->emailmicroid);

        $this->submit('save', _('Save'));
        
        $this->elementEnd('form');
        common_show_footer();
    }

    function get_confirmation()
    {
        $user = common_current_user();
        $confirm = new Confirm_address();
        $confirm->user_id = $user->id;
        $confirm->address_type = 'email';
        if ($confirm->find(true)) {
            return $confirm;
        } else {
            return null;
        }
    }

    function handle_post()
    {

        # CSRF protection
        $token = $this->trimmed('token');
        if (!$token || $token != common_session_token()) {
            $this->show_form(_('There was a problem with your session token. Try again, please.'));
            return;
        }

        if ($this->arg('save')) {
            $this->save_preferences();
        } else if ($this->arg('add')) {
            $this->add_address();
        } else if ($this->arg('cancel')) {
            $this->cancel_confirmation();
        } else if ($this->arg('remove')) {
            $this->remove_address();
        } else if ($this->arg('removeincoming')) {
            $this->remove_incoming();
        } else if ($this->arg('newincoming')) {
            $this->new_incoming();
        } else {
            $this->show_form(_('Unexpected form submission.'));
        }
    }

    function save_preferences()
    {

        $emailnotifysub = $this->boolean('emailnotifysub');
        $emailnotifyfav = $this->boolean('emailnotifyfav');
        $emailnotifymsg = $this->boolean('emailnotifymsg');
        $emailnotifynudge = $this->boolean('emailnotifynudge');
        $emailmicroid = $this->boolean('emailmicroid');
        $emailpost = $this->boolean('emailpost');

        $user = common_current_user();

        assert(!is_null($user)); # should already be checked

        $user->query('BEGIN');

        $original = clone($user);

        $user->emailnotifysub = $emailnotifysub;
        $user->emailnotifyfav = $emailnotifyfav;
        $user->emailnotifymsg = $emailnotifymsg;
        $user->emailnotifynudge = $emailnotifynudge;
        $user->emailmicroid = $emailmicroid;
        $user->emailpost = $emailpost;

        $result = $user->update($original);

        if ($result === false) {
            common_log_db_error($user, 'UPDATE', __FILE__);
            common_server_error(_('Couldn\'t update user.'));
            return;
        }

        $user->query('COMMIT');

        $this->show_form(_('Preferences saved.'), true);
    }

    function add_address()
    {

        $user = common_current_user();

        $email = $this->trimmed('email');

        # Some validation

        if (!$email) {
            $this->show_form(_('No email address.'));
            return;
        }

        $email = common_canonical_email($email);

        if (!$email) {
            $this->show_form(_('Cannot normalize that email address'));
            return;
        }
        if (!Validate::email($email, true)) {
            $this->show_form(_('Not a valid email address'));
            return;
        } else if ($user->email == $email) {
            $this->show_form(_('That is already your email address.'));
            return;
        } else if ($this->email_exists($email)) {
            $this->show_form(_('That email address already belongs to another user.'));
            return;
        }

          $confirm = new Confirm_address();
           $confirm->address = $email;
           $confirm->address_type = 'email';
           $confirm->user_id = $user->id;
           $confirm->code = common_confirmation_code(64);

        $result = $confirm->insert();

        if ($result === false) {
            common_log_db_error($confirm, 'INSERT', __FILE__);
            common_server_error(_('Couldn\'t insert confirmation code.'));
            return;
        }

        mail_confirm_address($user, $confirm->code, $user->nickname, $email);

        $msg = _('A confirmation code was sent to the email address you added. Check your inbox (and spam box!) for the code and instructions on how to use it.');

        $this->show_form($msg, true);
    }

    function cancel_confirmation()
    {
        $email = $this->arg('email');
        $confirm = $this->get_confirmation();
        if (!$confirm) {
            $this->show_form(_('No pending confirmation to cancel.'));
            return;
        }
        if ($confirm->address != $email) {
            $this->show_form(_('That is the wrong IM address.'));
            return;
        }

        $result = $confirm->delete();

        if (!$result) {
            common_log_db_error($confirm, 'DELETE', __FILE__);
            $this->server_error(_('Couldn\'t delete email confirmation.'));
            return;
        }

        $this->show_form(_('Confirmation cancelled.'), true);
    }

    function remove_address()
    {

        $user = common_current_user();
        $email = $this->arg('email');

        # Maybe an old tab open...?

        if ($user->email != $email) {
            $this->show_form(_('That is not your email address.'));
            return;
        }

        $user->query('BEGIN');
        $original = clone($user);
        $user->email = null;
        $result = $user->updateKeys($original);
        if (!$result) {
            common_log_db_error($user, 'UPDATE', __FILE__);
            common_server_error(_('Couldn\'t update user.'));
            return;
        }
        $user->query('COMMIT');

        $this->show_form(_('The address was removed.'), true);
    }

    function remove_incoming()
    {
        $user = common_current_user();
        
        if (!$user->incomingemail) {
            $this->show_form(_('No incoming email address.'));
            return;
        }
        
        $orig = clone($user);
        $user->incomingemail = null;

        if (!$user->updateKeys($orig)) {
            common_log_db_error($user, 'UPDATE', __FILE__);
            $this->server_error(_("Couldn't update user record."));
        }
        
        $this->show_form(_('Incoming email address removed.'), true);
    }

    function new_incoming()
    {
        $user = common_current_user();
        
        $orig = clone($user);
        $user->incomingemail = mail_new_incoming_address();
        
        if (!$user->updateKeys($orig)) {
            common_log_db_error($user, 'UPDATE', __FILE__);
            $this->server_error(_("Couldn't update user record."));
        }

        $this->show_form(_('New incoming email address added.'), true);
    }
    
    function email_exists($email)
    {
        $user = common_current_user();
        $other = User::staticGet('email', $email);
        if (!$other) {
            return false;
        } else {
            return $other->id != $user->id;
        }
    }
}
