            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Общие функции для админ-панели
        function showSaveIndicator(message = 'Изменения сохранены') {
            $('.save-indicator').text(message).fadeIn().delay(2000).fadeOut();
        }

        function showError(message) {
            alert(message);
        }

        // Обработка редактируемых полей
        $('.editable').on('click', function() {
            if ($(this).hasClass('editing')) return;
            
            const cell = $(this);
            const currentValue = cell.text().trim();
            const fieldType = cell.data('type');
            let input;

            if (fieldType === 'textarea') {
                input = $('<textarea>').addClass('form-control').val(currentValue);
            } else if (fieldType === 'number') {
                input = $('<input>').attr('type', 'number').addClass('form-control').val(currentValue);
            } else if (fieldType === 'email') {
                input = $('<input>').attr('type', 'email').addClass('form-control').val(currentValue);
            } else {
                input = $('<input>').attr('type', 'text').addClass('form-control').val(currentValue);
            }
            
            cell.html(input);
            cell.addClass('editing');
            input.focus();

            input.on('blur', function() {
                const newValue = input.val().trim();
                const updateUrl = cell.data('type').startsWith('feature') ? 'update_feature.php' : 'update_complex.php';
                
                if (newValue === currentValue) {
                    cell.html(currentValue);
                    cell.removeClass('editing');
                    return;
                }

                const data = {
                    id: cell.data('id'),
                    field: cell.data('field'),
                    value: newValue
                };

                if (cell.data('type').startsWith('feature')) {
                    data.category = cell.data('category');
                    data.feature = cell.data('feature');
                }

                $.ajax({
                    url: updateUrl,
                    method: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            cell.html(response.value);
                            showSaveIndicator(response.message);
                        } else {
                            showError(response.message);
                            cell.html(currentValue);
                        }
                    },
                    error: function(xhr) {
                        showError('Ошибка при сохранении: ' + xhr.statusText);
                        cell.html(currentValue);
                    },
                    complete: function() {
                        cell.removeClass('editing');
                    }
                });
            });

            input.on('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    input.blur();
                } else if (e.key === 'Escape') {
                    cell.html(currentValue);
                    cell.removeClass('editing');
                }
            });
        });

        // Подтверждение удаления
        $('[data-confirm]').on('click', function(e) {
            if (!confirm($(this).data('confirm'))) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html> 