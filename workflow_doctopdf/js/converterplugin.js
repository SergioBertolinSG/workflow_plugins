/**
 * ownCloud Workflow_DocToPdf
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Joas Schilling <nickvergessen@gmx.de>
 * @copyright Joas Schilling 2016
 *
 */

(function() {
	if (!OCA.Workflow_DocToPdf) {
		/**
		 * @namespace
		 */
		OCA.Workflow_DocToPdf = {};
	}

	OCA.Workflow_DocToPdf.ConverterPlugin = {

		/**
		 * Format actions for display
		 *
		 * @param {String} type of the flow
		 * @param {Object} actions Array with the actions
		 * @param {jQuery} $actions jQuery handle which can be filled with descriptions for the actions
		 * @return {Object} Array with the actions
		 */
		getActions: function (type, actions, $actions) {
			if (type !== 'workflow_doctopdf') {
				return actions;
			}

			return {
				convert: 'doctopdf'
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
			if (type !== 'workflow_doctopdf') {
				return;
			}

			$actions.text(t('workflow_doctopdf', 'Convert .doc to .pdf'));
		},

		/**
		 * Initialise the form to be ready for a new/edit action again
		 *
		 * @param {String} type the form is initialised with
		 * @param {Object} actions Array with the actions that have been set.
		 * @param {jQuery} $actions jQuery handle which can be filled with options for the actions
		 */
		initialiseForm: function (type, actions, $actions) {
			if (type !== 'workflow_doctopdf') {
				return;
			}

			var $label = $('<strong>');
			$label.text(t('workflow_doctopdf', 'Convert .doc to .pdf'));
			$actions.append($label);
		}
	};


})();

OC.Plugins.register('OCA.Workflow.Engine.Plugins', OCA.Workflow_DocToPdf.ConverterPlugin);
