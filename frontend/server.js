import serve from 'serve';
import path from 'path';

serve(path.join(__dirname, 'dist'), {
  port: process.env.PORT || 3000,
  single: true // 👈 this ensures SPA fallback to index.html
});
