import { defineConfig } from 'vite';
import symfonyPlugin from 'vite-plugin-symfony';
import inject from '@rollup/plugin-inject';

export default defineConfig({
    plugins: [
        // Automatically inject `import $ from 'jquery'` in any JS module that
        // references $ or jQuery — required for jquery-ui and other plugins.
        inject({
            $:       'jquery',
            jQuery:  'jquery',
            include: /\.[cm]?[jt]sx?$/,
        }),
        symfonyPlugin(),
    ],

    build: {
        rollupOptions: {
            input: {
                app:    './assets/js/app.js',
                editor: './assets/js/editor.js',
            },
        },
    },
});
