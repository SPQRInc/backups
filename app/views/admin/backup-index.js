window.backups = {

	el: '#backups',

	data: function () {
		return _.merge ({
			backups: false,
			progress: false,
			config: {
				filter: this.$session.get ('backups.filter', {order: 'date desc', limit: 25})
			},
			pages: 0,
			count: '',
			selected: [],
			validationattempts: 0
		}, window.$data);
	},
	ready: function () {
		this.resource = this.$resource ('api/backups/backup{/id}');

		this.$watch ('config.page', this.load, {immediate: true});
	},
	watch: {
		'config.filter': {
			handler: function (filter) {
				if (this.config.page) {
					this.config.page = 0;
				} else {
					this.load ();
				}

				this.$session.set ('backups.filter', filter);
			},
			deep: true
		}
	},
	computed: {
		statusOptions: function () {
			var options = _.map (this.$data.statuses, function (status, id) {
				return {text: status, value: id};
			});

			return [{label: this.$trans ('Filter by'), options: options}];
		}
	},
	methods: {
		active: function active(backup) {
			return this.selected.indexOf(backup.id) != -1;
		},
		save: function save(backup) {
			this.resource.save({ id: backup.id }, { backup: backup }).then(function () {
				this.load();
				this.$notify('Backup saved.');
			});
		},
		status: function status(_status) {

			var backups = this.getSelected();

			backups.forEach(function (backup) {
				backup.status = _status;
			});

			this.resource.save({ id: 'bulk' }, { backups: backups }).then(function () {
				this.load();
				this.$notify('Backups saved.');
			});
		},
		remove: function remove() {
			this.resource.delete({ id: 'bulk' }, { ids: this.selected }).then(function () {
				this.load();
				this.$notify('Backups deleted.');
			});
		},
		load: function load() {
			this.resource.query({ filter: this.config.filter, page: this.config.page }).then(function (res) {

				var data = res.data;

				this.$set('backups', data.backups);
				this.$set('pages', data.pages);
				this.$set('count', data.count);
				this.$set('selected', []);
			});
		},
		getSelected: function getSelected() {
			return this.backups.filter(function (backup) {
				return this.selected.indexOf(backup.id) !== -1;
			}, this);
		},
		removeBackups: function removeBackups() {
			this.resource.delete({ id: 'bulk' }, { ids: this.selected }).then(function () {
				this.load();
				this.$notify('Backups(s) deleted.');
			});
		},
		getStatusText: function getStatusText(backup) {
			return this.statuses[backup.status];
		},
		queueFiles: function queueFiles(id) {
			return this.$http.post('api/backups/backup/buildqueue', { id: id }).then(function (res) {
				if (res.data.files > 0) {
					return this.queueFiles(id);
				} else {
					return true;
				}
			}, function (data) {
				this.$notify(data.data.message, 'danger');
				this.progress = false;
				return false;
			});
		},
		archiveFiles: function archiveFiles(id) {
			return this.$http.post('api/backups/backup/archivefiles', { id: id }).then(function (res) {
				if (res.data.files > 0) {
					return this.archiveFiles(id);
				} else {
					return true;
				}
			}, function (data) {
				this.$notify(data.data.message, 'danger');
				this.progress = false;
				return false;
			});
		},
		archiveDatabase: function archiveDatabase(id) {
			return this.$http.post('api/backups/backup/archivedatabase', { id: id }).then(function (res) {
				return true;
			}, function (data) {
				this.$notify(data.data.message, 'danger');
				return false;
			});
		},
		archiveBundle: function archiveBundle(id) {
			return this.$http.post('api/backups/backup/archivebundle', { id: id }).then(function (res) {
				if (res.data.files > 0) {
					return this.archiveBundle(id);
				} else {
					return true;
				}
			}, function (data) {
				this.$notify(data.data.message, 'danger');
				this.progress = false;
				return false;
			});
		},
		prepareBackup: function prepareBackup(id) {
			return this.$http.post('api/backups/backup/preparebackup').then(function (res) {
				return res;
			}, function (res) {
				this.$notify(res.data, 'danger');
				this.progress = false;
			});
		},
		prepareBundle: function prepareBundle(id) {
			return this.$http.post('api/backups/backup/preparebundle', { id: id }).then(function (res) {
				return true;
			}, function (res) {
				this.$notify(res.data, 'danger');
				this.progress = false;
			});
		},
		storeBackup: function storeBackup(id) {
			return this.$http.post('api/backups/backup/storebackup', { id: id }).then(function (res) {
				return true;
			}, function (res) {
				this.$notify(res.data, 'danger');
				this.progress = false;
			});
		},
		purgeBackup: function purgeBackup(id) {
			return this.$http.post('api/backups/backup/purgebackup', { id: id }).then(function (res) {
				return true;
			}, function (res) {
				this.$notify(res.data, 'danger');
				this.progress = false;
			});
		},
		pruneBackup: function pruneBackup(id) {
			return this.$http.post('api/backups/backup/prunebackup', { id: id }).then(function (res) {
				return true;
			}, function (res) {
				this.$notify(res.data, 'danger');
				this.progress = false;
			});
		},
		finalizeBackup: function finalizeBackup(id) {
			return this.$http.post('api/backups/backup/finalizebackup', { id: id }).then(function (res) {
				return true;
			}, function (res) {
				this.$notify(res.data, 'danger');
				this.progress = false;
			});
		},
		performbackup: function performbackup() {
			this.progress = true;
			this.$notify('Backup-generation is in progress. Please stand by until the "Backup completed"-message shows up.', {
				status: 'warning',
				timeout: 0
			});
			this.prepareBackup().then(function (res) {
				this.load();
				this.queueFiles(res.data.backup.id).then(function () {
					this.archiveFiles(res.data.backup.id).then(function () {
						this.archiveDatabase(res.data.backup.id).then(function () {
							this.prepareBundle(res.data.backup.id).then(function () {
								this.archiveBundle(res.data.backup.id).then(function () {
									this.storeBackup(res.data.backup.id).then(function () {
										this.purgeBackup(res.data.backup.id).then(function () {
											this.pruneBackup(res.data.backup.id).then(function () {
												this.finalizeBackup(res.data.backup.id).then(function () {
													this.progress = false;
													this.load();
													this.$notify('Backup completed.', { status: 'success', timeout: 0 });
												});
											});
										});
									});
								});
							});
						});
					});
				});
			}, function (res) {
				this.$notify(data, 'danger');
				this.progress = false;
			});
		}
	},
	components: {}
};
Vue.ready (window.backups);
