/*
 * vim: ts=3 sw=3 et wrap co=100 go-=b
 */

$(document).ready
(
   function (ev)
   {
      var f = 'custom_form_section_purchase_bundle.js onReady()';
      UTILS.checkArgs(f, arguments, ['function']);

      try
      {
         new CustomFormSectionPurchaseBundle();
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }
);

/*
 * Event driven code for the Purchase Bundle Custom Form Section.
 */
function CustomFormSectionPurchaseBundle()
{
   var f = 'CustomFormSectionPurchaseBundle()';
   UTILS.checkArgs(f, arguments, []);

   // Private functions. ////////////////////////////////////////////////////////////////////////

   /*
    *
    */
   function _onChangeFieldElementState(ev)
   {
      try
      {
         var f = 'CustomFormSectionPurchaseBundle._onChangeFieldElementState()';
         UTILS.checkArgs(f, arguments, ['object']);

         _updatePurchaseBundleSection();
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _updatePurchaseBundleSection()
   {
      var f = 'CustomFormSectionPurchaseBundle._updatePurchaseBundleSection()';
      UTILS.checkArgs(f, arguments, []);

      if (_itemNameShortsToAddIfValueSelectedByValueByFieldId === null)
      {
         _initItemNameShortsToAddIfValueSelectedByValueByFieldId();
      }

      var itemNameShortsToAdd = [];

      for (var fieldId in _itemNameShortsToAddIfValueSelectedByValueByFieldId)
      {
         var field = document.getElementById(fieldId);

         if (!UTILS.DOM.elementIsDisplayed(field))
         {
            continue;
         }

         var selectedValue                             = $(field).val();
         var itemNameShortsToAddIfValueSelectedByValue =
         (
            _itemNameShortsToAddIfValueSelectedByValueByFieldId[fieldId]
         );

         if (typeof itemNameShortsToAddIfValueSelectedByValue[selectedValue] != 'undefined')
         {
            itemNameShortsToAdd = itemNameShortsToAdd.concat
            (
               itemNameShortsToAddIfValueSelectedByValue[selectedValue]
            );
         }
      }

      itemNameShortsToAdd = UTILS.array.unique(itemNameShortsToAdd);
      _updatePurchaseBundleCosts(itemNameShortsToAdd);
      _updatePurchaseBundleImages(itemNameShortsToAdd);
   }

   /*
    *
    */
   function _updatePurchaseBundleCosts(itemNameShortsToAdd)
   {
      var f = 'CustomFormSectionPurchaseBundle._updatePurchaseBundleCosts()';
      UTILS.checkArgs(f, arguments, ['array']);

      var costTotalByChargeFreq = {once: 0 , monthly: 0 , yearly: 0 };
      var trsToAddByChargeFreq  = {once: [], monthly: [], yearly: []};
      var costsTableJq          = $('#purchase-bundle-costs-table'         );
      var noItemsMessageDivJq   = $('#purchase-bundle-no-items-message-div');

      switch (itemNameShortsToAdd.length > 0)
      {
       case true : costsTableJq.show(); noItemsMessageDivJq.hide(); break;
       case false: costsTableJq.hide(); noItemsMessageDivJq.show(); break;
      }

      $('tr.cost-item-tr').remove();

      for (var i = 0; i < itemNameShortsToAdd.length; ++i)
      {
         var itemNameShort = itemNameShortsToAdd[i];
         var itemInfo      = _itemInfoByNameShort[itemNameShort];
         var chargeFreq    = itemInfo.chargeFrequencyNameShort;
         var cost          = itemInfo.itemChargeDollarsAus + itemInfo.itemChargeCentsAus / 100;

         costTotalByChargeFreq[chargeFreq] += cost;
         trsToAddByChargeFreq[chargeFreq].push
         (
            TR
            (
               {'class': 'cost-item-tr'},
               TD(itemInfo.itemNameLong), TD({'class': 'alignR'}, '$' + cost.toFixed(2))
            )
         );
      }

      var prefix = '#purchase-bundle-costs-';
      _insertElementsAfterElement(trsToAddByChargeFreq.once   , $(prefix +'heading-setup-tr'  )[0]);
      _insertElementsAfterElement(trsToAddByChargeFreq.monthly, $(prefix +'heading-monthly-tr')[0]);
      _insertElementsAfterElement(trsToAddByChargeFreq.yearly , $(prefix +'heading-yearly-tr' )[0]);

      $(prefix + 'setup-total-td'  ).text('$' + costTotalByChargeFreq.once.toFixed(2)   );
      $(prefix + 'monthly-total-td').text('$' + costTotalByChargeFreq.monthly.toFixed(2));
      $(prefix + 'yearly-total-td' ).text('$' + costTotalByChargeFreq.yearly.toFixed(2) );
   }

   /*
    *
    */
   function _updatePurchaseBundleImages(itemNameShortsToAdd)
   {
      var f = 'CustomFormSectionPurchaseBundle._updatePurchaseBundleImages()';
      UTILS.checkArgs(f, arguments, ['array']);

      var imagesDivJq = $('#purchase-bundle-images-div');

      imagesDivJq.empty();

      for (var i = 0; i < itemNameShortsToAdd.length; ++i)
      {
         var itemNameShort = itemNameShortsToAdd[i];
         var itemInfo      = _itemInfoByNameShort[itemNameShort];
         var imageSrc      =
         (
            Config.PATH_TO_PROJECT_ROOT_FROM_WEB_ROOT + '/' + itemInfo.itemImageFilename
         );

         imagesDivJq.append(IMG({alt: itemInfo.itemNameLong, src: imageSrc}));
      }
   }

   /*
    *
    */
   function _insertElementsAfterElement(elementsToInsert, element)
   {
      var f = 'CustomFormSectionPurchaseBundle._insertElementsAfterElement()';
      UTILS.checkArgs(f, arguments, ['array', 'Defined']);

      // Note Regarding Element Order
      // ----------------------------
      // Insert after element in reverse order so that
      // final result is that original order is maintained.

      for (var i = elementsToInsert.length - 1; i >= 0; --i)
      {
         $(elementsToInsert[i]).insertAfter(element);
      }
   }

   /*
    *
    */
   function _initItemNameShortsToAddIfValueSelectedByValueByFieldId()
   {
      var f =
      (
         'CustomFormSectionPurchaseBundle._initItemNameShortsToAddIfValueSelectedByValueByFieldId()'
      );
      UTILS.checkArgs(f, arguments, []);

      _itemNameShortsToAddIfValueSelectedByValueByFieldId = {};

      var sections = _form.sections;

      for (var i = 0, lenI = sections.length; i < lenI; ++i)
      {
         var section = sections[i];

         if (section.type == 'custom')
         {
            continue;
         }

         var fields = section.fields;

         for (var j = 0, lenJ = fields.length; j < lenJ; ++j)
         {
            var field                      = fields[j];
            var itemNameShortsToAddByValue = {};
            var boolAnyItemsFound          = false;
            var fieldType                  = field.type;
            var fieldId                    = FormUtils.getFieldIdAttributeFromSectionNameFieldName
            (
               section.nameShort,field.name
            );

            switch (fieldType)
            {
             case 'text'     : // Fall through.
             case 'textarea' : // Fall through.
             case 'paragraph':
               // Do nothing.
               break;

             case 'select':
               var options = field.options;
               for (var k = 0; k < options.length; ++k)
               {
                  var option = options[k];
                  if (option.itemNameShortsToAddIfSelected !== undefined)
                  {
                     var itemNameShorts = option.itemNameShortsToAddIfSelected;
                     if (itemNameShorts.length > 0)
                     {
                        itemNameShortsToAddByValue[option.text] = itemNameShorts;
                        boolAnyItemsFound = true;
                     }
                  }
               }
               break;

             default:
               throw new Exception(f, 'Unknown field type "' + fieldType + '".', '');
            }

            if (boolAnyItemsFound)
            {
               _itemNameShortsToAddIfValueSelectedByValueByFieldId[fieldId] =
               (
                  itemNameShortsToAddByValue
               );
            }
         }
      }
   }

   /*
    *
    */
   function _init()
   {
      var f = 'CustomFormSectionPurchaseBundle._init()';
      UTILS.checkArgs(f, arguments, []);

      var mainFormJq = $('#main-form');
      mainFormJq.find('input'   ).change(_onChangeFieldElementState);
      mainFormJq.find('select'  ).change(_onChangeFieldElementState);
      mainFormJq.find('textarea').change(_onChangeFieldElementState);

      _updatePurchaseBundleSection();
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var _form                                               = window.FORM_DEFINITION;
   var _itemNameShortsToAddIfValueSelectedByValueByFieldId = null;
   var _itemInfoByNameShort                                = window.ITEM_INFO_BY_NAME_SHORT;

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init();
}
