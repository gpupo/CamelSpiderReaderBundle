

/* Inicialização do layout em todas as páginas*/
jQuery(document).ready(function(){
    $(".tabs").tabs();
    $(".auto.accordion").accordion();

    /** Show/Hide do sidebar **/
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

    /** news **/
    $(".news.accordion").accordion({
       navigation: false,
       icons: false,
       collapsible: true,
       animated: true
    });

    //clique no título da news
    $(".news h3 a.title").click(function(){
        if($(this).parent().hasClass('ui-state-active')) {
            console.log('Clique em notícia aberta');
            if ($(this).hasClass('zoom')) {
                console.log('recolhendo notícia');
                $(this).removeClass('zoom');
                $(this).blur();
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
                return false;
            }
        }
    });

});
