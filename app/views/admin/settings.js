window.settings = {

	el: '#settings',

	data: {
		config: $data.config,
		database: $data.database
	},

	methods: {

		save: function () {
			this.$http.post('admin/backups/save', {config: this.config}, function () {
				this.$notify('Settings saved.');
			}).error(function (data) {
				this.$notify(data, 'danger');
			});
		},
		add: function add(e) {
			e.preventDefault();
			if (!this.newExclusion) return;

			this.config.exclusions.push(this.newExclusion);
			this.newExclusion = '';
		},
		remove: function (exclusion) {
			this.config.exclusions.$remove(exclusion);
		}
	},
	components: {}
};

Vue.ready(window.settings);