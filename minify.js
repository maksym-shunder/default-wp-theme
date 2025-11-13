const fs = require('fs')
const path = require('path')
const {minify} = require('terser')
const CleanCSS = require('clean-css')

// рекурсивний пошук файлів з певним розширенням
function getFiles(dir, ext) {
	let results = []
	fs.readdirSync(dir).forEach(file => {
		const filePath = path.join(dir, file)
		const stat = fs.statSync(filePath)
		if(stat && stat.isDirectory()) {
			results = results.concat(getFiles(filePath, ext))
		} else if(file.endsWith(ext)) {
			results.push(filePath)
		}
	})
	return results
}

// мінімізація всіх JS файлів паралельно
async function minifyJS(dir) {
	const files = getFiles(dir, '.js')
	await Promise.all(
		files.map(async filePath => {
			const code = fs.readFileSync(filePath, 'utf8')
			const result = await minify(code)
			fs.writeFileSync(filePath, result.code, 'utf8')
			console.log(`Minified JS: ${filePath}`)
		})
	)
}

// мінімізація всіх CSS файлів паралельно
function minifyCSS(dir) {
	const files = getFiles(dir, '.css')
	files.forEach(filePath => {
		const code = fs.readFileSync(filePath, 'utf8')
		const output = new CleanCSS().minify(code)
		fs.writeFileSync(filePath, output.styles, 'utf8')
		console.log(`Minified CSS: ${filePath}`)
	})
}

// основні папки
const foldersToMinify = [
	'./assets/js',
	'./assets/css',
	'./template-parts/gutenberg-blocks'
];

// виконуємо мінімізацію
(async() => {
	for(const folder of foldersToMinify) {
		await minifyJS(folder)
		minifyCSS(folder)
	}
	console.log('✅ All files minified')
})()
