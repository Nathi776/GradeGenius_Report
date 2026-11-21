import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), "");
    const allowedHosts = (env.VITE_ALLOWED_HOSTS ?? "")
        .split(",")
        .map((host) => host.trim())
        .filter(Boolean);

    return {
        plugins: [
            laravel({
                input: ["resources/css/app.css", "resources/js/app.js"],
                refresh: true,
                fonts: [
                    bunny("Instrument Sans", {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            tailwindcss(),
        ],
        server: {
            allowedHosts,
            watch: {
                ignored: ["**/storage/framework/views/**"],
            },
        },
    };
});
