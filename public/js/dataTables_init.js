$(document).ready(function() {
	$('.dataTable').dataTable( {
         "iDisplayLength": -1,
         "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
	 "sPaginationType": "full_numbers",
    "oLanguage": {
      "sEmptyTable":      "Sem dados disponíveis na tabela",
      "sInfo":            "Exibindo de _START_ a _END_ do total de _TOTAL_ registros",
      "sInfoEmpty":       "Sem registros a exibir",
      "sInfoFiltered":    " - filtrado do total de _MAX_ registros",
      "sInfoPostFix":     "", /* appended to sInfo at all times */
      "sInfoThousands":   ".",
      "sLengthMenu":      "Exibir até _MENU_ registros",
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
