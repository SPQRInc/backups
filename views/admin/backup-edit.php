<?php $view->script(
	'backup-edit',
	'spqr/backups:app/bundle/backup-edit.js',
	[
		'vue'
	]
) ?>

<form id="backup" class="uk-form" v-validator="form" @submit.prevent="save | valid" v-cloak>
	<div class="uk-margin uk-flex uk-flex-space-between uk-flex-wrap" data-uk-margin>
		<div data-uk-margin>
			<h2 class="uk-margin-remove">{{ 'Edit Backup' | trans }}</h2>
		</div>
		<div data-uk-margin>
			<a class="uk-button uk-margin-small-right"
			   :href="$url.route('admin/backups/backup')">{{ backup.id ? 'Close' : 'Cancel' | trans }}</a>
			<button class="uk-button uk-button-primary" type="submit">{{ 'Save' | trans }}</button>
		</div>
	</div>
	<ul class="uk-tab" v-el:tab v-show="sections.length > 1">
		<li v-for="section in sections"><a>{{ section.label | trans }}</a></li>
	</ul>
	<div class="uk-switcher uk-margin" v-el:content>
		<div v-for="section in sections">
			<component :is="section.name"
			           :backup.sync="backup"
			           :data.sync="data"
			           :form="form"></component>
		</div>
	</div>
</form>