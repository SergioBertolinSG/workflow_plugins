/**
 * ownCloud Workflow_Notification
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Joas Schilling <nickvergessen@gmx.de>
 * @copyright Joas Schilling 2016
 *
 */

(function() {
	if (!OCA.Workflow_Notification) {
		/**
		 * @namespace
		 */
		OCA.Workflow_Notification = {};
	}

	OCA.Workflow_Notification.SendEmailPlugin = {

		_triggers: {
			createFile: t('workflow_notification', 'File creation'),
			deleteFile: t('workflow_notification', 'File deletion')
		},

		_targets: {
			owner: t('workflow_notification', 'File owner')
		},

		/**
		 * Format actions for display
		 *
		 * @param {String} type of the flow
		 * @param {Object} actions Array with the actions
		 * @param {jQuery} $actions jQuery handle which can be filled with descriptions for the actions
		 * @return {Object} Array with the actions
		 */
		getActions: function (type, actions, $actions) {
			if (type !== 'workflow_notification') {
				return actions;
			}

			return {
				send: 'email',
				trigger: $actions.find('.notification-trigger').val(),
				target: $actions.find('.notification-target').val()
			};
		},

		/**
		 * Format actions for display
		 *
		 * @param {String} type of the flow
		 * @param {Object} actions Array with the actions
		 * @param {jQuery} $actions jQuery handle which can be filled with descriptions for the actions
		 */
		formatActionsForDisplay: function (type, actions, $actions) {
			if (type !== 'workflow_notification') {
				return;
			}

			var trigger = actions.trigger || 'createFile';
			var target = actions.target || 'owner';

			$actions.html(t('workflow_notification', 'Send email notification on {trigger} to {target}', {
				trigger: '<strong>' + this._triggers[trigger] + '</strong>',
				target: '<strong>' + this._targets[target] + '</strong>'
			}, undefined, {escape: false}));
		},

		/**
		 * Initialise the form to be ready for a new/edit action again
		 *
		 * @param {String} type the form is initialised with
		 * @param {Object} actions Array with the actions that have been set.
		 * @param {jQuery} $actions jQuery handle which can be filled with options for the actions
		 */
		initialiseForm: function (type, actions, $actions) {
			if (type !== 'workflow_notification') {
				return;
			}

			var trigger = actions.trigger || 'createFile';
			var target = actions.target || 'owner';

			var $trigger = $('<select>').addClass('notification-trigger');
			_.each(this._triggers, function(description, key) {
				var $option = $('<option>');
				$option.attr('value', key).text(description);
				if (trigger === key) {
					$option.attr('selected', true);
				}
				$trigger.append($option);
			});

			var $target = $('<select>').addClass('notification-target');
			_.each(this._targets, function(description, key) {
				var $option = $('<option>');
				$option.attr('value', key).text(description);
				if (target === key) {
					$option.attr('selected', true);
				}
				$target.append($option);
			});

			var $notificationOptions = $('<div>');
			$notificationOptions.html(t('workflow_notification', 'Send email notification on {trigger} to {target}', {
				trigger: $trigger[0].outerHTML,
				target: $target[0].outerHTML
			}, undefined, {escape: false}));

			var $label = $('<strong>').text(t('workflow_notification', 'Notification:'));
			$actions.append($label);
			$actions.append($notificationOptions);
		}
	};


})();

OC.Plugins.register('OCA.Workflow.Engine.Plugins', OCA.Workflow_Notification.SendEmailPlugin);
