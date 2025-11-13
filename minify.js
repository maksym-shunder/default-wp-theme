const fs = require('fs')
const path = require('path')
const {minify} = require('terser')
const csso = require('csso')

function getFiles(dir, ext) {
	let results = []
	if(!fs.existsSync(dir)) return results
	fs.readdirSync(dir).forEach(file => {
		const filePath = path.join(dir, file)
		const stat = fs.statSync(filePath)
		if(stat.isDirectory()) {
			results = results.concat(getFiles(filePath, ext))
		} else if(file.endsWith(ext)) {
			results.push(filePath)
		}
	})
	return results
}

async function minifyJS(dir) {
	const files = getFiles(dir, '.js')
	await Promise.all(
		files.map(async filePath => {
			if(filePath.endsWith('.min.js')) {
				console.log(`Skipped already minified JS: ${filePath}`)
				return
			}
			const code = fs.readFileSync(filePath, 'utf8')
			try {
				const result = await minify(code)
				if(result.code) {
					fs.writeFileSync(filePath, result.code, 'utf8')
					console.log(`Minified JS: ${filePath}`)
				}
			} catch(err) {
				console.error(`❌ JS minify error in ${filePath}:`, err.message)
			}
		})
	)
}

function minifyCSS(dir) {
	const files = getFiles(dir, '.css')
	files.forEach(filePath => {
		if(filePath.endsWith('.min.css')) {
			console.log(`Skipped already minified CSS: ${filePath}`)
			return
		}
		const code = fs.readFileSync(filePath, 'utf8')
		try {
			const output = csso.minify(code, {restructure: false}).css
			fs.writeFileSync(filePath, output, 'utf8')
			console.log(`Minified CSS: ${filePath}`)
		} catch(err) {
			console.error(`❌ CSS minify error in ${filePath}:`, err.message)
		}
	})
}

const foldersToMinify = [
	'./assets/js',
	'./assets/css',
	'./template-parts/gutenberg-blocks'
];

(async() => {
	for(const folder of foldersToMinify) {
		await minifyJS(folder)
		minifyCSS(folder)
	}
	console.log('✅ All files minified safely')
})()
