module.exports = {
	main: {
		options: {
			mode: 'zip',
			archive: './release/unmask.<%= pkg.version %>.zip'
		},
		expand: true,
		cwd: 'release/<%= pkg.version %>/',
		src: ['**/*'],
		dest: 'unmask/'
	}
};