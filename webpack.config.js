module.exports = [
	{
		entry: {
			"settings": "./app/views/admin/settings.js",
			"backup-index": "./app/views/admin/backup-index",
			"backup-edit": "./app/views/admin/backup-edit"
		},
		output: {
			filename: "./app/bundle/[name].js"
		},
		module: {
			loaders: [
				{test: /\.vue$/, loader: "vue"},
				{test: /\.js$/, exclude: /node_modules/, loader: "babel-loader"}
			]
		}
	}
];