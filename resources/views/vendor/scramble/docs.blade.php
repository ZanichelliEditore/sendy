<!doctype html>
<html lang="en" data-theme="{{ $config->renderer()->get('theme', 'light') }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="color-scheme" content="{{ $config->renderer()->get('theme', 'light') }}">
  <title>{{ $config->get('ui.title') ?? config('app.name') . ' - API Docs' }}</title>

  <script src="https://unpkg.com/@stoplight/elements@8.4.2/web-components.min.js"></script>
  <link rel="stylesheet" href="https://unpkg.com/@stoplight/elements@8.4.2/styles.min.css">

  <script>
    const originalFetch = window.fetch;

    // intercept TryIt requests and add the XSRF-TOKEN header,
    // which is necessary for Sanctum cookie-based authentication to work correctly
    // ALSO add OAuth2 Bearer token if available
    window.fetch = (url, options) => {
      const CSRF_TOKEN_COOKIE_KEY = "XSRF-TOKEN";
      const CSRF_TOKEN_HEADER_KEY = "X-XSRF-TOKEN";
      const getCookieValue = (key) => {
        const cookie = document.cookie.split(';').find((cookie) => cookie.trim().startsWith(key));
        return cookie?.split("=")[1];
      };

      const updateFetchHeaders = (
        headers,
        headerKey,
        headerValue,
      ) => {
        if (headers instanceof Headers) {
          headers.set(headerKey, headerValue);
        } else if (Array.isArray(headers)) {
          headers.push([headerKey, headerValue]);
        } else if (headers) {
          headers[headerKey] = headerValue;
        }
      };

      const hasHeader = (headers, headerKey) => {
        if (headers instanceof Headers) {
          return headers.has(headerKey);
        } else if (Array.isArray(headers)) {
          return headers.some(([key]) => key.toLowerCase() === headerKey.toLowerCase());
        } else if (headers) {
          return Object.keys(headers).some((key) => key.toLowerCase() === headerKey.toLowerCase());
        }
        return false;
      };

      const {
        headers = new Headers()
      } = options || {};

      // Add CSRF token
      const csrfToken = getCookieValue(CSRF_TOKEN_COOKIE_KEY);
      if (csrfToken) {
        updateFetchHeaders(headers, CSRF_TOKEN_HEADER_KEY, decodeURIComponent(csrfToken));
      }

      // Add OAuth2 Bearer token if available, but don't clobber an Authorization
      // header already set for another security scheme (e.g. Basic Auth or an API key)
      if (window.oauth2Token && !hasHeader(headers, 'Authorization')) {
        updateFetchHeaders(headers, 'Authorization', `Bearer ${window.oauth2Token}`);
      }

      return originalFetch(url, {
        ...options,
        headers,
      });
    };
  </script>

  <style>
    html,
    body {
      margin: 0;
      height: 100%;
    }

    body {
      background-color: var(--color-canvas);
    }

    /* issues about the dark theme of stoplight/mosaic-code-viewer using web component:
         * https://github.com/stoplightio/elements/issues/2188#issuecomment-1485461965
         */
    [data-theme="dark"] .token.property {
      color: rgb(128, 203, 196) !important;
    }

    [data-theme="dark"] .token.operator {
      color: rgb(255, 123, 114) !important;
    }

    [data-theme="dark"] .token.number {
      color: rgb(247, 140, 108) !important;
    }

    [data-theme="dark"] .token.string {
      color: rgb(165, 214, 255) !important;
    }

    [data-theme="dark"] .token.boolean {
      color: rgb(121, 192, 255) !important;
    }

    [data-theme="dark"] .token.punctuation {
      color: #dbdbdb !important;
    }
  </style>
</head>

<body style="height: 100vh; overflow-y: hidden">

  <!-- OAuth2 Toggle Button -->
  <button id="oauth-toggle-btn" style="position: fixed; top: 20px; right: 20px; background: #1f2937; color: #10b981; border: 2px solid #10b981; border-radius: 6px; padding: 8px 16px; cursor: pointer; font-size: 14px; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.4); z-index: 1001; display: flex; align-items: center; gap: 8px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; transition: all 0.2s ease;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
      <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
    </svg>
    Authorize
  </button>

  <!-- OAuth2 Authentication Panel (collapsed by default) -->
  <div id="oauth-panel" style="position: fixed; top: 20px; right: 20px; background: #1f2937; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); z-index: 1000; width: 320px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
      <h3 style="margin: 0; color: #fff; font-size: 16px; font-weight: 600;">OAuth2 Authentication</h3>
      <button id="oauth-close-btn" style="background: transparent; border: none; color: #9ca3af; cursor: pointer; font-size: 24px; line-height: 1; padding: 0; width: 24px; height: 24px;">&times;</button>
    </div>

    <label style="display: block; color: #9ca3af; font-size: 12px; margin-bottom: 4px;">Client ID</label>
    <input type="text" id="client-id" value="1" style="width: 100%; padding: 8px; margin-bottom: 10px; border-radius: 4px; border: 1px solid #374151; background: #111827; color: #fff; font-size: 14px; box-sizing: border-box;">

    <label style="display: block; color: #9ca3af; font-size: 12px; margin-bottom: 4px;">Client Secret</label>
    <input type="password" id="client-secret" value="secretOAuth2Example" style="width: 100%; padding: 8px; margin-bottom: 15px; border-radius: 4px; border: 1px solid #374151; background: #111827; color: #fff; font-size: 14px; box-sizing: border-box;">

    <button id="get-token-btn" style="width: 100%; padding: 10px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 500;">
      Get Token
    </button>

    <div id="token-display" style="margin-top: 15px; display: none;">
      <label style="display: block; color: #9ca3af; font-size: 12px; margin-bottom: 4px;">Access Token</label>
      <textarea id="token-value" readonly style="width: 100%; height: 80px; padding: 8px; margin-bottom: 10px; border-radius: 4px; border: 1px solid #374151; background: #111827; color: #10b981; font-size: 11px; font-family: 'Courier New', monospace; resize: none; box-sizing: border-box;"></textarea>
      <div style="display: flex; gap: 6px; flex-wrap: wrap;">
        <button id="use-token-btn" style="flex: 1; min-width: 80px; padding: 8px 4px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">
          Use Token
        </button>
        <button id="copy-token-btn" style="flex: 1; min-width: 80px; padding: 8px 4px; background: #10b981; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">
          Copy Token
        </button>
        <button id="delete-token-btn" style="flex: 1; min-width: 80px; padding: 8px 4px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 500;">
          Delete Token
        </button>
      </div>
    </div>

    <div id="error-display" style="margin-top: 10px; padding: 8px; background: #7f1d1d; border-radius: 4px; color: #fca5a5; font-size: 12px; display: none;"></div>
  </div>

  <elements-api
    id="docs"
    @foreach($config->renderer()->all(except: ['theme']) as $key => $value)
    @continue(! $value)
    {{ $key }}="{{ $value === true ? 'true' : ($value === false ? 'false' : $value) }}"
    @endforeach
    />
    <script>
      (async () => {
        const docs = document.getElementById('docs');
        docs.apiDescriptionDocument = @json($spec);
      })();
    </script>

    @if($config->renderer()->get('theme', 'light') === 'system')
    <script>
      var mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

      function updateTheme(e) {
        if (e.matches) {
          window.document.documentElement.setAttribute('data-theme', 'dark');
          window.document.getElementsByName('color-scheme')[0].setAttribute('content', 'dark');
        } else {
          window.document.documentElement.setAttribute('data-theme', 'light');
          window.document.getElementsByName('color-scheme')[0].setAttribute('content', 'light');
        }
      }

      mediaQuery.addEventListener('change', updateTheme);
      updateTheme(mediaQuery);
    </script>
    @endif

    <script>
      // OAuth2 Panel JavaScript
      (function() {
        // Store the OAuth2 token globally
        window.oauth2Token = null;

        const getCookie = (name) => {
          const value = `; ${document.cookie}`;
          const parts = value.split(`; ${name}=`);
          if (parts.length === 2) return parts.pop().split(';').shift();
        };

        // Toggle panel visibility
        const oauthPanel = document.getElementById('oauth-panel');
        const toggleBtn = document.getElementById('oauth-toggle-btn');
        const closeBtn = document.getElementById('oauth-close-btn');

        // Hover effects for Authorize button
        toggleBtn.addEventListener('mouseenter', () => {
          toggleBtn.style.background = '#10b981';
          toggleBtn.style.color = '#111827';
          toggleBtn.style.borderColor = '#10b981';
        });
        toggleBtn.addEventListener('mouseleave', () => {
          toggleBtn.style.background = '#1f2937';
          toggleBtn.style.color = '#10b981';
          toggleBtn.style.borderColor = '#10b981';
        });

        toggleBtn.addEventListener('click', () => {
          const isVisible = oauthPanel.style.display !== 'none';
          oauthPanel.style.display = isVisible ? 'none' : 'block';
          toggleBtn.style.display = isVisible ? 'flex' : 'none';
        });

        closeBtn.addEventListener('click', () => {
          oauthPanel.style.display = 'none';
          toggleBtn.style.display = 'flex';
        });

        document.getElementById('get-token-btn').addEventListener('click', async () => {
          const clientId = document.getElementById('client-id').value.trim();
          const clientSecret = document.getElementById('client-secret').value.trim();
          const errorDisplay = document.getElementById('error-display');
          const tokenDisplay = document.getElementById('token-display');
          const getTokenBtn = document.getElementById('get-token-btn');

          // Reset displays
          errorDisplay.style.display = 'none';
          tokenDisplay.style.display = 'none';

          // Validate inputs
          if (!clientId || !clientSecret) {
            errorDisplay.textContent = 'Please enter both Client ID and Client Secret';
            errorDisplay.style.display = 'block';
            return;
          }

          // Disable button during request
          getTokenBtn.disabled = true;
          getTokenBtn.textContent = 'Getting Token...';

          try {
            const response = await fetch('/oauth/token', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-XSRF-TOKEN': decodeURIComponent(getCookie('XSRF-TOKEN') || '')
              },
              body: `grant_type=client_credentials&client_id=${encodeURIComponent(clientId)}&client_secret=${encodeURIComponent(clientSecret)}`
            });

            const data = await response.json();

            if (response.ok && data.access_token) {
              // Store token globally for fetch interceptor
              window.oauth2Token = data.access_token;
              document.getElementById('token-value').value = data.access_token;
              tokenDisplay.style.display = 'block';
            } else {
              errorDisplay.textContent = data.message || data.error || 'Failed to obtain token';
              errorDisplay.style.display = 'block';
            }
          } catch (error) {
            errorDisplay.textContent = 'Network error: ' + error.message;
            errorDisplay.style.display = 'block';
          } finally {
            // Re-enable button
            getTokenBtn.disabled = false;
            getTokenBtn.textContent = 'Get Token';
          }
        });

        // Function to update Stoplight Elements Authorization fields
        function updateStoplightAuthDisplay() {
          // Read token from textarea instead of window.oauth2Token
          const tokenValue = document.getElementById('token-value').value.trim();
          if (!tokenValue) return;

          const token = tokenValue;
          const bearerToken = `Bearer ${token}`;

          // Also update window.oauth2Token to keep it in sync
          window.oauth2Token = token;

          // Wait for Stoplight Elements to render
          setTimeout(() => {
            // Try multiple selectors to find authorization input fields
            const selectors = [
              'input[placeholder*="Bearer"]',
              'input[placeholder*="Authorization"]',
              'input[placeholder*="token"]',
              'input[name*="Authorization"]',
              'input[name*="authorization"]',
              '[class*="auth"] input[type="text"]',
              '[class*="Auth"] input[type="text"]',
              'input[type="text"][value*="Bearer"]'
            ];

            let updated = false;
            for (const selector of selectors) {
              const inputs = document.querySelectorAll(selector);
              inputs.forEach(input => {
                if (input.placeholder && (input.placeholder.includes('Bearer') || input.placeholder.includes('Authorization'))) {
                  input.value = bearerToken;
                  // Trigger input event to notify Stoplight Elements
                  input.dispatchEvent(new Event('input', {
                    bubbles: true
                  }));
                  input.dispatchEvent(new Event('change', {
                    bubbles: true
                  }));
                  updated = true;
                }
              });
            }

            // Also check for pre/code blocks showing curl examples
            const codeBlocks = document.querySelectorAll('code, pre');
            codeBlocks.forEach(block => {
              if (block.textContent && block.textContent.includes('Authorization:') && block.textContent.includes('Bearer 123')) {
                const tokenPreview = token.substring(0, 50) + '...';
                block.textContent = block.textContent.replace(/Bearer 123/g, `Bearer ${tokenPreview}`);
              }
            });

            console.log('Stoplight Auth fields updated:', updated);
          }, 500);
        }

        document.getElementById('use-token-btn').addEventListener('click', () => {
          // Read token from textarea and update both Stoplight and window.oauth2Token
          const tokenValue = document.getElementById('token-value').value.trim();

          if (!tokenValue) {
            return;
          }

          // Update window.oauth2Token from textarea value
          window.oauth2Token = tokenValue;

          const btn = document.getElementById('use-token-btn');
          const originalText = btn.textContent;
          const originalBg = btn.style.background;

          btn.textContent = 'Applied!';
          btn.style.background = '#059669';

          // Update Stoplight Elements display (which now reads from textarea)
          updateStoplightAuthDisplay();

          setTimeout(() => {
            // Close the panel after showing feedback
            oauthPanel.style.display = 'none';
            toggleBtn.style.display = 'flex';

            // Reset button after closing
            setTimeout(() => {
              btn.textContent = originalText;
              btn.style.background = originalBg;
            }, 100);
          }, 1000);
        });

        document.getElementById('copy-token-btn').addEventListener('click', () => {
          const tokenValue = document.getElementById('token-value');
          tokenValue.select();
          tokenValue.setSelectionRange(0, 99999); // For mobile devices

          // Try modern clipboard API first
          if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(tokenValue.value).then(() => {
              showCopyFeedback();
            }).catch(() => {
              // Fallback to execCommand
              document.execCommand('copy');
              showCopyFeedback();
            });
          } else {
            // Fallback to execCommand
            document.execCommand('copy');
            showCopyFeedback();
          }
        });

        function showCopyFeedback() {
          const btn = document.getElementById('copy-token-btn');
          const originalText = btn.textContent;
          const originalBg = btn.style.background;

          btn.textContent = 'Copied!';
          btn.style.background = '#059669';

          setTimeout(() => {
            // Close the panel after showing feedback
            oauthPanel.style.display = 'none';
            toggleBtn.style.display = 'flex';

            // Reset button after closing
            setTimeout(() => {
              btn.textContent = originalText;
              btn.style.background = originalBg;
            }, 100);
          }, 1000);
        }

        document.getElementById('delete-token-btn').addEventListener('click', () => {
          // Clear the in-memory token used by the fetch interceptor
          window.oauth2Token = null;

          // Clear the textarea
          document.getElementById('token-value').value = '';

          // Clear any Stoplight Elements authorization inputs that were populated
          const selectors = [
            'input[placeholder*="Bearer"]',
            'input[placeholder*="Authorization"]',
            'input[placeholder*="token"]',
            'input[name*="Authorization"]',
            'input[name*="authorization"]',
            '[class*="auth"] input[type="text"]',
            '[class*="Auth"] input[type="text"]',
          ];
          selectors.forEach((selector) => {
            document.querySelectorAll(selector).forEach((input) => {
              input.value = '';
              input.dispatchEvent(new Event('input', {
                bubbles: true
              }));
              input.dispatchEvent(new Event('change', {
                bubbles: true
              }));
            });
          });

          showDeleteFeedback();
        });

        function tokenDisplayHide() {
          document.getElementById('token-display').style.display = 'none';
        }

        function showDeleteFeedback() {
          const btn = document.getElementById('delete-token-btn');
          const originalText = btn.textContent;
          const originalBg = btn.style.background;

          btn.textContent = 'Deleted!';
          btn.style.background = '#991b1b';

          setTimeout(() => {
            // Close the panel after showing feedback
            oauthPanel.style.display = 'none';
            toggleBtn.style.display = 'flex';
            tokenDisplayHide();

            // Reset button after closing
            setTimeout(() => {
              btn.textContent = originalText;
              btn.style.background = originalBg;
            }, 100);
          }, 1000);
        }

        // Sync textarea token-value changes to window.oauth2Token
        document.getElementById('token-value').addEventListener('input', (e) => {
          const newToken = e.target.value.trim();
          if (newToken) {
            window.oauth2Token = newToken;
          } else {
            window.oauth2Token = null;
          }
        });

        // ====== REVERSE SYNC: Stoplight Elements Input → window.oauth2Token ======
        // Listen for changes in Stoplight-generated auth inputs and sync back to window.oauth2Token
        (function setupStoplightTokenSync() {
          // Use event delegation to handle dynamically created inputs
          document.addEventListener('input', (e) => {
            const target = e.target;

            // Check if this is a Stoplight auth input by:
            // 1. ID pattern: id_auth_Token_* (dynamic suffix)
            // 2. OR name/placeholder patterns for authorization
            const isStoplightAuthInput =
              (target.id && target.id.match(/^id_auth_Token_/)) ||
              (target.name && target.name.toLowerCase().includes('authorization')) ||
              (target.placeholder && (
                target.placeholder.includes('Bearer') ||
                target.placeholder.includes('Authorization') ||
                target.placeholder.toLowerCase().includes('token')
              ));

            if (isStoplightAuthInput && target.tagName === 'INPUT') {
              let newToken = target.value.trim();

              // Remove "Bearer " prefix if user includes it
              newToken = newToken.replace(/^Bearer\s+/i, '');

              if (newToken) {
                window.oauth2Token = newToken;
              } else {
                window.oauth2Token = null;
              }
            }
          }, true); // Use capture phase to ensure early interception            
        })();

        // Allow Enter key to submit
        document.getElementById('client-id').addEventListener('keypress', (e) => {
          if (e.key === 'Enter') document.getElementById('get-token-btn').click();
        });
        document.getElementById('client-secret').addEventListener('keypress', (e) => {
          if (e.key === 'Enter') document.getElementById('get-token-btn').click();
        });
      })();
    </script>

</body>

</html>