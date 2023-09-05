const path = require('path')

module.exports = {
    context: path.resolve(__dirname, './'),
    resolve: {
        extensions: ['.js', '.ts', '.vue', '.json'],
        alias: {
            '@/': path.resolve('src'),
        }
    }
}