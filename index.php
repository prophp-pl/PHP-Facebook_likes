<!doctype html>
<html lang="pl">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
        <title>System reakcji - samouczek ProPHP</title>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <style>
            #rating a {
                display: block;
                width: 64px;
                height: 64px;
            }
            
            #rating #like {
                background: transparent url('img/likeit.png') no-repeat 0 -64px;
            }
            
            #rating #like.on {
                background-position-y: 0;
            }
            
            .message {
                padding: 5px;
                background-color: red;
                color: #fff;
                display: none;
                position: fixed;
                top: 0;
                right: 0;
                padding: 10px;
                
            }
        </style>
        <script>
            $(function() {
                // URL naszego skryptu
                var url = 'likes_ajax.php';
                // Numer strony - na sztywno podana testowo wartość 1
                var pageId = $('#rating').data('pageid');
                // Nazwa klasy aktywnej / nieaktywnej
                var classStates = {
                    0: 'off',
                    1: 'on'
                };
                // Parsowanie odpowiedzi XHR
                var parseResponse = function (result) {
                    // jeśli odpowiedź nie jest pusta
                    if (!$.isEmptyObject(result)) {
                        // potrzebny nam będzie identyfikator a#id
                        // zmapujemy go z danych zawartych w obiekcie result.data
                        // zmienna `key` będzie tym kluczem - w przykładzie wartość `like`
                        for (var key in result.data) {
                            // wartość klucza 0 lub 1
                            var value = result.data[key];
                            // wyczyścimy wszystkie klasy on oraz off aby nie kolidowały
                            $.each(classStates, function(i, v){
                                $('#' + key).removeClass(v);
                            });
                            // do odpowiedniego identyfikatora (a#like) dodamy klasę on lub off
                            // zależną od wartości pobranej z bazy - 0 lub 1
                            $('#' + key).addClass(classStates[value]);
                        }                        
                    }
                };
                
                // Pobranie danych początkowych
                $.ajax({
                    url: url,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'select',
                        pageId: pageId
                    }
                })
                .done(function(result) {
                    parseResponse(result);
                })
                .fail(function(jqXHR) {
                    $('.message').html(jqXHR.responseJSON.message).show();
                });
                
                
                $('#rating').on('click', 'a', function(e) {
                    e.preventDefault();

                    // Aktualizacja danych
                    $.ajax({
                        url: url,
                        method: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'save',
                            pageId: pageId,
                            element: $(this).attr('id')
                        }
                    })
                    .done(function(result) {
                        parseResponse(result);
                    })
                    .fail(function(jqXHR) {
                        $('.message').html(jqXHR.responseJSON.message).show();
                    });
                });
            });
        </script>
    </head>
    <body>
        <main>
            <div class="message"></div>
            <div id="rating" data-pageid="1">
                <a href="#" id="like"></a>
            </div>
        </main>
    </body>
</html>