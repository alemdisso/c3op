$(document).ready(function() {
	$('.dataTable').dataTable( {
	  "sPaginationType": "full_numbers",
    "oLanguage": {
      "sEmptyTable":      "Sem dados disponíveis na tabela",
      "sInfo":            "Exibindo de _START_ até _END_ de _TOTAL_ registros",
      "sInfoEmpty":       "Sem registros a exibir",
      "sInfoFiltered":    " - filtrado do total de _MAX_ registros",
      "sInfoPostFix":     "", /* appended to sInfo at all times */
      "sInfoThousands":   ".",
      "sLengthMenu":      "Exibir _MENU_ registros",
      "sLoadingRecords":  "Aguarde - carregando...",
      "sProcessing":      "Aguarde - processando...",
      "sSearch":          "Filtrar:",
      "sUrl":             "",
      "sZeroRecords":     "Sem resultados para a busca",
      "oPaginate": {
        "sFirst":         "Primeiro",
        "sPrevious":      "Anterior",
        "sNext":          "Próximo",
        "sLast":          "Último"
      }
    }
  } );
} );
