/**
 * CSRFトークンを非同期で取得する関数
 * @returns {Promise<string>} トークン文字列
 */
async function fetchToken(baseURL) {
  const response = await fetch(baseURL+"/auth/fetch_csrf_token.php", {
    method: 'POST',
    credentials: 'include',
    headers: {
      'Accept': 'application/json'
    }
  });

  if (!response.ok) {
    throw new Error(`トークン取得失敗: ${response.status}`);
  }

  const data = await response.json();
  return data.data.csrf_token; 
}

/**
 * .csrf_token クラスを持つ要素すべてに取得したトークンを埋め込む関数
 */
async function autoInjectCsrfToken(baseURL) {
  try {
    const token = await fetchToken(baseURL);
    
    // class="csrf_token" の要素をすべて取得
    const targetElements = document.querySelectorAll('.csrf_token');

    targetElements.forEach((el) => {
      if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
        el.value = token;
      } else {
        el.textContent = token;
      }
    });

    window.dispatchEvent(new Event('csrf-token-ready'));
  } catch (error) {
    console.error('CSRFトークンの自動埋め込みに失敗しました:', error);
    window.dispatchEvent(new Event('csrf-token-error'));
  }
}