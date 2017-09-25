<?php $view->script( 'backup-index', 'spqr/backups:app/bundle/backup-index.js', 'vue' ); ?>
<div id="backups" class="uk-form" v-cloak>
	<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
		<div class="uk-flex uk-flex-middle uk-flex-wrap" data-uk-margin>
			<h2 class="uk-margin-remove"
			    v-if="!selected.length">{{ '{0} %count% Backups|{1} %count% Backup|]1,Inf[ %count% Backups' | transChoice count {count:count} }}</h2>
			<template v-else>
				<h2 class="uk-margin-remove">{{ '{1} %count% Backup selected|]1,Inf[ %count% Backups selected' | transChoice selected.length {count:selected.length} }}</h2>
				<div class="uk-margin-left">
					<ul class="uk-subnav pk-subnav-icon">
						<li>
							<a class="pk-icon-delete pk-icon-hover"
							   title="Delete"
							   data-uk-tooltip="{delay: 500}"
							   @click="remove" v-confirm="'Delete Backups?'"></a>
						</li>
					</ul>
				</div>
			</template>
			<div class="pk-search">
				<div class="uk-search">
					<input class="uk-search-field" type="text" v-model="config.filter.search" debounce="300">
				</div>
			</div>
		</div>
		<div data-uk-margin>
			<button v-if="!progress" class="uk-button uk-button-primary" @click.prevent="performbackup">
				<span>{{ 'Perform Backup' | trans }}</span>
			</button>
			<button v-else class="uk-button uk-button-primary" disabled>
				<span><i class="uk-icon-spinner uk-icon-spin"></i> {{ 'Saving files' | trans }}</span>
			</button>
		</div>
	</div>
	<div class="uk-overflow-container">
		<table class="uk-table uk-table-hover uk-table-middle">
			<thead>
			<tr>
				<th class="pk-table-width-minimum">
					<input type="checkbox" v-check-all:selected.literal="input[name=id]" number></th>
				<th class="pk-table-min-width-200"
				    v-order:filename="config.filter.order">{{ 'Filename' | trans }}
				</th>
				<th class="pk-table-width-100 uk-text-center">
					<input-filter :title="$trans('Status')"
					              :value.sync="config.filter.status"
					              :options="statusOptions"></input-filter>
				</th>
				<th class="pk-table-width-100" v-order:date="config.filter.order">{{ 'Date' | trans }}</th>
			</tr>
			</thead>
			<tbody>
			<tr class="check-item" v-for="backup in backups" :class="{'uk-active': active(backup)}">
				<td><input type="checkbox" name="id" :value="backup.id"></td>
				<td>
					<a :href="$url.route('admin/backups/backup/edit', { id: backup.id })">{{ backup.filename }}</a>
				</td>
				<td class="uk-text-center">
					<i :title="getStatusText(backup)" :class="{
                                'pk-icon-circle-danger': backup.status == 2,
                                'pk-icon-circle-success': backup.status == 1,
                                'pk-icon-circle': backup.status == 0

                            }"></i>
				</td>
				<td>
					{{ backup.date | date }}
				</td>
			</tr>
			</tbody>
		</table>
	</div>

	<h3 class="uk-h1 uk-text-muted uk-text-center"
	    v-show="backups && !backups.length">{{ 'No Backups found.' | trans }}</h3>
	<v-pagination :page.sync="config.page" :pages="pages" v-show="pages > 1 || page > 0"></v-pagination>
</div>