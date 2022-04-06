window.get_magic = () => {
  jQuery.ajax({
    type: "GET",
    data: { action: 'get', parts: jsObject.parts },
    contentType: "application/json; charset=utf-8",
    dataType: "json",
    url: jsObject.root + jsObject.parts.root + '/' + jsObject.parts.type,
    beforeSend: function (xhr) {
      xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
    }
  })
  .done(function(data){
    window.load_magic( data )
  })
  .fail(function(e) {
    console.log(e)
    jQuery('#error').html(e)
  })
}
window.get_magic()

window.load_magic = ( data ) => {
  let content = jQuery('#api-content')
  let spinner = jQuery('.loading-spinner')

  content.empty()
  let html = ``
  data.forEach(v=>{
    html += `
         <div class="cell">
             ${window.lodash.escape(v.name)}
         </div>
     `
  })
  content.html(html)

  spinner.removeClass('active')

}

$('.dt_date_picker').datepicker({
  constrainInput: false,
  dateFormat: 'yy-mm-dd',
  changeMonth: true,
  changeYear: true,
  yearRange: "1900:2050",
}).each(function() {
  if (this.value && moment.unix(this.value).isValid()) {
    this.value = window.SHAREDFUNCTIONS.formatDate(this.value);
  }
})


$('#submit-form').on("click", function (){
  $(this).addClass("loading")
  let start_date = $('#start_date').val()
  let comment = $('#comment-input').val()
  let update = {
    start_date,
    comment
  }

  window.makeRequest( "POST", jsObject.parts.type, { parts: jsObject.parts, update }, jsObject.parts.root + '/v1/' ).done(function(data){
    window.location.reload()
  })
  .fail(function(e) {
    console.log(e)
    jQuery('#error').html(e)
  })
})
