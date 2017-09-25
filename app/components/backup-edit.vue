<template>
    <div class="uk-grid pk-grid-large pk-width-sidebar-large uk-form-stacked" data-uk-grid-margin>
        <div class="pk-width-content">
            <div class="uk-form-row">
                <input class="uk-width-1-1 uk-form-large" type="text" name="filename" :placeholder="'Filename' | trans"
                       v-model="backup.filename" :disabled="true">
            </div>
        </div>
        <div class="pk-width-sidebar">
            <div class="uk-panel">
                <div class="uk-form-row">
                    <label for="form-status" class="uk-form-label">{{ 'Status' | trans }}</label>
                    <div class="uk-form-controls">
                        <select id="form-status" class="uk-width-1-1" v-model="backup.status">
                            <option v-for="(id, status) in data.statuses" :value="id" :disabled="true">{{ status }}</option>
                        </select>
                    </div>
                </div>
                <div class="uk-form-row">
                    <label for="form-type" class="uk-form-label">{{ 'Type' | trans }}</label>
                    <div id="form-type" class="uk-badge">{{ backup.type }}</div>
                </div>
                <div class="uk-form-row">
                    <span class="uk-form-label">{{ 'Date' | trans }}</span>
                    <div class="uk-form-controls">
                        <time :datetime="backup.date">{{ backup.date | date "longDate"}}</time>
                    </div>
                </div>
                <div class="uk-form-row">
                    <label for="form-backup_linked" class="uk-form-label">{{ 'Link file' | trans
                        }}</label>
                    <div class="uk-form-controls uk-form-controls-text">
                        <input id="form-backup_linked" type="checkbox" v-model="backup.data.backup.linked">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

module.exports = {

	props: ['backup', 'data', 'form'],

	section: {
		label: 'Item'
	}

};

</script>