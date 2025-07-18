import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';

import './index.css';

// Get the root container from the HTML
const container = document.getElementById('root');

// Create a root for the React app
const root = createRoot(container);

// Render the App component into the root
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);