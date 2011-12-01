

/* Inicialização do layout em todas as páginas*/
jQuery(document).ready(function(){
    $(".tabs").tabs();

    /** Show/Hide do sidebar
    $(".sidebar .bar").click(function() {
        $(".sidebar .content").toggle('drop');
        if($("#main").hasClass("expanded")) {
            console.log("Exibindo sidebar");
            $("#main").removeClass("expanded");
            $("#main").addClass("normal");
        } else {
            console.log("Ocultando sidebar");
            $("#main").addClass("expanded");
            $("#main").removeClass("normal");
        }

        return false;
    });
    **/
    /** news nav **/

    //$(".nav-news.accordion").accordion({active: 0}); // o valor de ativo indica qual dos tópicos fica aberto por padrão

    /** content **/
    $(".news h3 a.title").click(function(){
        if($(this).parent().hasClass('ui-state-active')) {
            console.log('Clique em notícia aberta');
            if ($(this).hasClass('zoom')) {
                console.log('recolhendo notícia');
                $(this).removeClass('zoom');
                $(this).parent().toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
                $(this).parent().next().toggleClass("ui-accordion-content-active");
            } else {
                console.log('Exibindo toda a notícia em '+$(this).attr('id'));
                if ($(this).hasClass('complete')){
                    console.log('Content loaded');
                } else {
                    //Carregar por ajax
                    target_xpath="div.content."+this.id+" div.txt";
                    console.log('Request new content for '+target_xpath);
                    $.get($(this).attr('href')+'.ajax', function(data) {
                          $(target_xpath).html(data);
                          console.log('Request Ajax finished');
                    });
                    $(this).addClass('complete');
                }
                $(this).addClass('zoom');
            }
        } else {
            console.log('Clique em notícia fechada');
            $(this).parent()
                .toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
                .next().addClass("ui-accordion-content-active");
        }

        return false;
    });

    //stars
    $(".classificacao .stars .stars-wrapper").stars({
        inputType: "select",
        cancelShow: false,
        callback: function(ui, type, value){
            console.log("stars value:"+value);
            news_id = ui.options.name.replace('vote_','');
            console.log("News ID:"+news_id);
            //Envia o voto para o controller
            $.post($("#url_submit_vote").val(), {vote:{rate: value, news_id: news_id}}, function(json)
            {
                if(json.responseCode == 200){
                    //Recebe a média de votação da news
                    average_select=".global .stars #form_vote_average_"+json.news_id+" .stars-wrapper";
                    $(average_select).stars("select", json.average);
                }
            },'json');
        }
    });

    $(".global .stars .stars-wrapper").stars({
        inputType: "select",
        disabled: true
    });

    //send to email
    $(".ui-button.news_send").click(function(){
        $("#send_to_email_window" ).dialog({ modal: true, width: 800, height: 600  });
        $("#send_to_email_window #send_to_iframe").attr("src", this.href);
        return false;
    });


});
