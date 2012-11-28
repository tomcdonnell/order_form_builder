/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

/*
 *
 */
var FormUtils =
{
   /*
    *
    */
   getFieldIdAttributeFromSectionNameFieldName: function (sectionName, fieldName)
   {
      var f = 'FormUtils.getFieldIdAttributeFromSectionNameFieldName()';
      UTILS.checkArgs(f, arguments, ['string', 'string']);

      return (sectionName + '|' + fieldName).replace(' ', '_', 'g');
   },

   /*
    *
    */
   getFieldsetCssClassFromSectionName: function (sectionName)
   {
      var f = 'FormUtils.getFieldsetCssClassFromSectionName()';
      UTILS.checkArgs(f, arguments, ['string']);

      return 'form-section form-section-' + sectionName.replace(' ', '-', 'g').toLowerCase();
   },

   /*
    *
    */
   getOptionMatchingValueFromField: function (optionValue, field)
   {
      var f = 'FormUtils.getOptionMatchingValueFromField()';
      UTILS.checkArgs(f, arguments, ['string', 'object']);

      if (field.type != 'select')
      {
         throw new Exception('Attempted to get option from non-select field.');
      }

      var options = field.options;

      for (var i = 0, len = options.length; i < len; ++i)
      {
         var option = options[i];

         if (optionValue == option.text)
         {
            return option;
         }
      }

      throw new Exception('Value "' + optionValue + '" not found in options for select field.');
   }
};
