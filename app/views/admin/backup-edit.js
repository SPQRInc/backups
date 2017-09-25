window.backup = {

	el: '#backup',

	data: function () {
		return {
			data: window.$data,
			backup: window.$data.backup,
			sections: []
		}
	},

	created: function () {

		var sections = [];

		_.forIn(this.$options.components, function (component, name) {

			var options = component.options || {};

			if (options.section) {
				sections.push(_.extend({name: name, priority: 0}, options.section));
			}

		});

		this.$set('sections', _.sortBy(sections, 'priority'));

		this.resource = this.$resource('api/backups/backup{/id}');
	},

	ready: function () {
		this.tab = UIkit.tab(this.$els.tab, {connect: this.$els.content});
	},

	methods: {

		save: function () {
			var data = {backup: this.backup, id: this.backup.id};

			this.$broadcast ('save', data);

			this.resource.save ({id: this.backup.id}, data).then (function (res) {

				var data = res.data;

				if (!this.backup.id) {
					window.history.replaceState ({}, '', this.$url.route ('admin/backups/backup/edit', {id: data.backup.id}))
				}

				this.$set ('backup', data.backup);

				this.$notify ('Backup saved.');

			}, function (res) {
				this.$notify (res.data, 'danger');
			});
		}

	},

	components: {
		settings: require('../../components/backup-edit.vue')
	}
};

Vue.ready(window.backup);