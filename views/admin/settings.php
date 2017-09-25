<?php $view->script( 'settings', 'spqr/backups:app/bundle/settings.js', [ 'vue', 'uikit-form-password' ] ); ?>

<div id="settings" class="uk-form uk-form-horizontal" v-cloak>
	<div class="uk-grid pk-grid-large" data-uk-grid-margin>
		<div class="pk-width-sidebar">
			<div class="uk-panel">
				<ul class="uk-nav uk-nav-side pk-nav-large" data-uk-tab="{ connect: '#tab-content' }">
					<li><a><i class="pk-icon-large-settings uk-margin-right"></i> {{ 'General' | trans }}</a></li>
					<li><a><i class="pk-icon-large-server uk-margin-right"></i>
							{{ 'Backup Servers' | trans }}</a>
					</li>
					<li><a><i class="pk-icon-large-database uk-margin-right"></i>
							{{ 'Database' | trans }}</a>
					</li>
					<li><a><i class="pk-icon-large-lock-file uk-margin-right"></i> {{ 'Exclusions' | trans }}</a></li>
				</ul>
			</div>
		</div>
		<div class="pk-width-content">
			<ul id="tab-content" class="uk-switcher uk-margin">
				<li>
					<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
						<div data-uk-margin>
							<h2 class="uk-margin-remove">{{ 'General' | trans }}</h2>
						</div>
						<div data-uk-margin>
							<button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}
							</button>
						</div>
					</div>
					<div class="uk-form-row">
						<label for="form-overwrite_memorylimit" class="uk-form-label">{{ 'Overwrite Memorylimit' | trans
							}}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<input id="form-overwrite_memorylimit" type="checkbox" v-model="config.overwrite_memorylimit">
						</div>
					</div>
					<div class="uk-form-row" v-if="config.overwrite_memorylimit">
						<label for="form-memorylimit" class="uk-form-label">{{ 'Memorylimit in MB' | trans }}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<p class="uk-form-controls-condensed">
								<input id="form-memorylimit" type="number" class="uk-form-width-large"
								       v-model="config.memorylimit" min="0" number>
							</p>
						</div>
					</div>
					<div class="uk-form-row">
						<label for="form-overwrite_executiontime" class="uk-form-label">{{ 'Overwrite Executiontime' |
							trans
							}}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<input id="form-overwrite_executiontime" type="checkbox" v-model="config.overwrite_executiontime">
						</div>
					</div>
					<div class="uk-form-row" v-if="config.overwrite_executiontime">
						<label for="form-executiontime" class="uk-form-label">{{ 'Executiontime in Seconds' | trans
							}}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<p class="uk-form-controls-condensed">
								<input id="form-executiontime" type="number" class="uk-form-width-large"
								       v-model="config.executiontime" min="0" number>
							</p>
						</div>
					</div>
					<div class="uk-form-row">
						<label for="form-process_files" class="uk-form-label">{{ 'Process Files per Loop' | trans
							}}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<p class="uk-form-controls-condensed">
								<input id="form-process_files" type="number" class="uk-form-width-large"
								       v-model="config.process_files" min="1" number>
							</p>
						</div>
					</div>
				</li>
				<li>
					<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
						<div data-uk-margin>
							<h2 class="uk-margin-remove">{{ 'Backup Servers' | trans }}</h2>
						</div>
						<div data-uk-margin>
							<button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}
							</button>
						</div>
					</div>
					<div class="uk-form-row">
						<label for="form-backup_method" class="uk-form-label">{{ 'Backup Method' | trans
							}}</label>
						<div class="uk-form-controls">
							<select id="form-backup_method" class="uk-form-width-large" v-model="config.backup_method">
								<option value="local">{{ 'Local' | trans }}</option>
								<option value="ftp">{{ 'FTP' | trans }}</option>
							</select>
						</div>
					</div>
					<div v-if="config.backup_method == 'ftp'">
						<div class="uk-form-row">
							<label for="form-ftp_host" class="uk-form-label">{{ 'FTP Server' | trans }}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<p class="uk-form-controls-condensed">
									<input id="form-ftp_host" type="text" class="uk-form-width-large" v-model="config.ftp.host">
								</p>
							</div>
						</div>
						<div class="uk-form-row">
							<label for="form-ftp_port" class="uk-form-label">{{ 'Port' | trans }}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<p class="uk-form-controls-condensed">
									<input id="form-ftp_port" type="number" class="uk-form-width-large"
									       v-model="config.ftp.port" min="0" number>
								</p>
							</div>
						</div>
						<div class="uk-form-row">
							<label for="form-ftp_ssl" class="uk-form-label">{{ 'Enable SSL Mode' | trans
								}}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<input id="form-ftp_ssl" type="checkbox" v-model="config.ftp.ssl">
							</div>
						</div>
						<div class="uk-form-row">
							<label for="form-ftp_passive" class="uk-form-label">{{ 'Enable Passive Mode' | trans
								}}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<input id="form-ftp_passive" type="checkbox" v-model="config.ftp.passive">
							</div>
						</div>
						<div class="uk-form-row">
							<label for="form-ftp_directory" class="uk-form-label">{{ 'Directory' | trans }}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<p class="uk-form-controls-condensed">
									<input id="form-ftp_directory" type="text" class="uk-form-width-large"
									       v-model="config.ftp.directory">
								</p>
							</div>
						</div>
						<div class="uk-form-row">
							<label for="form-ftp_username" class="uk-form-label">{{ 'Username' | trans }}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<p class="uk-form-controls-condensed">
									<input id="form-ftp_username" type="text" class="uk-form-width-large" v-model="config.ftp.user">
								</p>
							</div>
						</div>
						<div class="uk-form-row">
							<span class="uk-form-label">{{ 'Password' | trans }}</span>
							<div class="uk-form-controls uk-form-controls-text">
								<div class="uk-form-password">
									<p class="uk-form-controls-condensed">
										<input class="uk-form-password uk-form-width-large"
										       type="password"
										       name="password"
										       autocomplete="off"
										       v-model="config.ftp.password"> <a
												class="uk-form-password-toggle"
												href=""
												data-uk-form-password="{ lblShow: 'Show', lblHide: 'Hide' }">{{ 'Show' | trans }}</a>
									</p>
								</div>
							</div>
						</div>
					</div>
					<div v-if="config.backup_method == 'local'">
						<div class="uk-form-row">
							<label for="form-local_path" class="uk-form-label">{{ 'Path' | trans }}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<p class="uk-form-controls-condensed">
									<input id="form-local_path" type="text" class="uk-form-width-large"
									       v-model="config.local.path">
								</p>
							</div>
						</div>
					</div>
					<div class="uk-form-row">
						<label for="form-auto_prune" class="uk-form-label">{{ 'Prune Backups' | trans
							}}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<input id="form-auto_prune" type="checkbox" v-model="config.auto_prune">
						</div>
					</div>
					<div class="uk-form-row" v-if="config.auto_prune">
						<label for="form-backup_number" class="uk-form-label">{{ 'Number Of Backups To Keep' | trans
							}}</label>
						<div class="uk-form-controls uk-form-controls-text">
							<p class="uk-form-controls-condensed">
								<input id="form-backup_number" type="number" class="uk-form-width-large"
								       v-model="config.backup_number" min="0" number>
							</p>
						</div>
					</div>
				</li>
				<li>
					<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
						<div data-uk-margin>
							<h2 class="uk-margin-remove">{{ 'Database' | trans }}</h2>
						</div>
						<div data-uk-margin>
							<button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}
							</button>
						</div>
					</div>
					<div v-if="database != 'sqlite'">
						<div class="uk-form-row">
							<label for="form-database_use_mysqldump" class="uk-form-label">{{ 'Use Mysqldump' | trans
								}}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<input id="form-database_use_mysqldump" type="checkbox" v-model="config.database.usemysqldump">
							</div>
						</div>
						<div class="uk-form-row" v-if="config.database.usemysqldump">
							<label for="form-database_mysqldump" class="uk-form-label">{{ 'Mysqldump Binary Path' |
								trans
								}}</label>
							<div class="uk-form-controls uk-form-controls-text">
								<p class="uk-form-controls-condensed">
									<input id="form-database_mysqldump" type="text" class="uk-form-width-large"
									       v-model="config.database.mysqldump">
								</p>
							</div>
						</div>
					</div>
					<div v-if="database == 'sqlite'">
						<div class="uk-alert">{{ 'You are using SQLite, so you do not have to specify additional
							settings' | trans }}</div>
					</div>
				</li>
				<li>
					<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
						<div data-uk-margin>
							<h2 class="uk-margin-remove">{{ 'Exclusions' | trans }}</h2>
						</div>
						<div data-uk-margin>
							<button class="uk-button uk-button-primary" @click.prevent="save">{{ 'Save' | trans }}
							</button>
						</div>
					</div>
					<form class="uk-form uk-form-stacked" v-validator="formExclusions" @submit.prevent="add | valid">
						<div class="uk-form-row">
							<div class="uk-grid" data-uk-margin>
								<div class="uk-width-large-1-2">
									<input class="uk-input-large"
									       type="text"
									       placeholder="{{ 'Path' | trans }}"
									       name="exclusion"
									       v-model="newExclusion"
									       v-validate:required>
									<p class="uk-form-help-block uk-text-danger" v-show="formExclusions.exclusion.invalid">
										{{ 'Invalid value.' | trans }}</p>
								</div>
								<div class="uk-width-large-1-2">
									<div class="uk-form-controls">
										<span class="uk-align-right">
											<button class="uk-button" @click.prevent="add | valid">
												{{ 'Add' | trans }}
											</button>
										</span>
									</div>
								</div>
							</div>
						</div>
					</form>
					<hr />
					<div class="uk-alert"
					     v-if="!config.exclusions.length">{{ 'You can add your first exclusion using the input field above. Go ahead!' | trans }}
					</div>
					<ul class="uk-list uk-list-line" v-if="config.exclusions.length">
						<li v-for="exclusion in config.exclusions">
							<input class="uk-input-large"
							       type="text"
							       placeholder="{{ 'Path' | trans }}"
							       v-model="exclusion">
							<span class="uk-align-right">
								<button @click="remove(exclusion)" class="uk-button uk-button-danger">
									<i class="uk-icon-remove"></i>
								</button>
							</span>
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</div>