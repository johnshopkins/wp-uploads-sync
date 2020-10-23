const buttons = document.querySelectorAll('table.sync-status button.rerun');

for (var i = 0; i < buttons.length; i++) {
  console.log(buttons[i])
  buttons[i].addEventListener('click', function (e) {
    e.preventDefault();

    const data = {
      action: 'rerun_gearman_job',
      id: e.target.dataset.id,
      crop: e.target.dataset.crop
    };

    wp.apiFetch({
      url: wp.url.addQueryArgs('/wp/wp-admin/admin-ajax.php', data)
    }).then(data => {
      e.target.outerHTML = '<span class="spinner is-active"></span>';
    });

  });
}
