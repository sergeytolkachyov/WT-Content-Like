/**
 * @package     WT Content like
 * @copyright   Copyright (C) 2023-2023 Sergey Tolkachyov. All rights reserved.
 * @author      Sergey Tolkachyov - https://web-tolk.ru
 * @link 		https://web-tolk.ru
 * @version 	1.1.2
 * @license     GNU General Public License version 2 or later
 */
if (document.readyState != 'loading') {
	wt_content_like();
} else {
	document.addEventListener('DOMContentLoaded', wt_content_like);
}

function wt_content_like() {
	let elements = document.querySelectorAll('.wt_content_like_btn');

	Array.prototype.forEach.call(elements, function (el, i) {
		el.addEventListener('click', async function () {
			let articleId = el.getAttribute('data-article-id');
			let response  = await fetch(window.location.protocol + "//" + window.location.host + '/index.php?option=com_ajax&plugin=wt_content_like&group=content&format=raw&article_id=' + articleId);

			if (response.ok){
				let json = await response.json();

				if (json !== '') {
					if (json.success == 1) {
						let rating_count_span = document.getElementById('wt_content_like_rating_count_' + articleId);
						rating_count_span.removeAttribute('style');
						rating_count_span.innerHTML = json.rating;

						let rating_message_span = document.getElementById('wt_content_like_meesage_' + articleId);
						rating_message_span.innerHTML = json.message;
						rating_message_span.setAttribute('style','color:#008000;');

					} else {
						let rating_message_span = document.getElementById('wt_content_like_meesage_' + articleId);
						rating_message_span.innerHTML = json.message;
						rating_message_span.setAttribute('style','color:#FF0000;');
					}
				}
			}
		}, false);//addEventListener
	});


}