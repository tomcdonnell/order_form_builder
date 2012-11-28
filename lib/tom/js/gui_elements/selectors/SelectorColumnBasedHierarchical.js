/**************************************************************************************************\
*
* vim: ts=3 sw=3 et wrap co=100 go-=b
*
* Filename: "SelectorColumnBasedHierarchical.js"
*
* Project: General.
*
* Purpose: A GUI widget allowing items existing in a hierarchy of categories to be browsed and
*          selected.  Any number of hierarchy levels may be used.  Each hierarchy level (referred
*          to in the code as a category level) has its own column.  Columns are arranged from left
*          (most general hierarchy level) to right (most specific hierarchy level).  All hierarchy
*          details are set in the PHP file whose URL is specified in the ajaxUrl parameter.
*
*
* Description and Purpose of Collapsible Options
* ----------------------------------------------
*
* In order to understand the following section, familiarity with the user interface of the
* SelectorColumnBasedHierarchical object may be necessary.  Hopefully the reader will have access
* to a working interface.
*
* The SelectorColumnBasedHierarchical GUI object was developed to be used generally, but
* specifically for the assignment of staff groups within a large organisation to particular items
* (activities, responsibilities, etc.).
*
* The large organisation for which this GUI object was developed had a staff group hierarchy that
* allowed each staff member to be defined as being a member of a single staff group.  The single
* staff group could be at any level of the hierarchy.  The first four hierarchy levels from most
* general to most specific were named Division, Branch, Section, and Sub-section.
*
* For example, a staff member could be defined as being a member of the BioSciences Research
* Division, or as being a member of the Relationship Management Sub-section of the Service &
* Process Section of the Service Delivery Unit Branch of the Business and Corporate Services
* Division.
*
* For the hierarchy described above, the SelectorColumnBasedHierarchical GUI object would have five
* columns, labelled: Division, Branch, Section, Sub-section, and Individual.  For an individual
* who is a member of a particular division without being a member of any of the branches, sections,
* or sub-sections contained within that division, if the individual is to be displayed in the
* Individual column, then in the three columns between Division and Individual, a blank option
* would have to be displayed.  The concept of collapsible options was introduced so that the blank
* options would not have to be displayed.
*
* 'Collapsing' the blank options means to display an option that would normally have a blank parent
* option at the hierarchy level of that blank parent, in replacement of that blank parent.  For the
* case of the staff member who belongs to a particular division without belonging to any branch,
* section, or sub-section of that division, this would mean displaying the individual in the
* column labelled Branch.  In general, an implication of treating blank options as collapsible
* is that options may be displayed in columns for which the column heading does not match the
* nature of the option (eg. the option from the Individual column being displayed in the Branch
* column.
*
* Collapsible options work as follows.  For each option sent by the server to the client, a non-
* collapsed option id is sent as well as a collapsed option id.  Both option ids are concatenated
* strings consisting of the name of the option, plus the name of its ancestor options, separated by
* pipe characters ('|').  An example of an optionId and its corresponding optionIdCollapsed follow.
*
* Example:
*   optionId         : 'Energy Sector Development Division||Earth Resources Policy||23001122'
*   optionIdCollapsed: 'Energy Sector Development Division|Earth Resources Policy|23001122'
*
* The number at the most-specific category level is the id of a particular staff member.  Note that
* in the example above the category levels containing blank option text (at the second and fourth
* category levels) have in the optionIdCollapsed been removed.  The optionIdCollapsed determines
* where the option will be displayed by the client, and the optionId should be used only when
* preparing messages to send to the server.
*
* Author: Tom McDonnell 2010-09-03.
*
\**************************************************************************************************/

// Object definition. //////////////////////////////////////////////////////////////////////////////

/*
 *
 */
function SelectorColumnBasedHierarchical(
   idAssignmentItem, ajaxUrl, onFinishAutoLoadingOptions, displayAjaxFailureMessageFunction,
   boolViewOnlyMode
)
{
   var f = 'SelectorColumnBasedHierarchical()';
   UTILS.checkArgs(f, arguments, ['nullOrInt', 'nullOrString', Function, Function, Boolean]);

   // Privileged functions. /////////////////////////////////////////////////////////////////////

   // Getters. --------------------------------------------------------------------------------//

   this.getTable           = function () {return _domElements.tables.main;};
   this.getNCategoryLevels = function () {return _state.nCategoryLevels  ;};

   /*
    * See comment on recursive slave function.
    *
    * IMPORTANT
    * ---------
    * Note also that this function and its slave function deal with optionIds, not
    * optionIdsCollapsed.  The reason is that the fully and partially checked optionIds are to
    * be sent to the server.  The collapsed optionIds exist only for the client.
    */
   this.getFullyAndPartiallyCheckedOptionIds = function ()
   {
      var f = 'SelectorColumnBasedHierarchical.getFullyAndPartiallyCheckedOptionIds()';
      UTILS.checkArgs(f, arguments, []);

      // Get the optionIds at the lowest category level, then find all the
      // checked options by inspecting the children of those options recursively.
      var selectorTsc    = _guiElements.selectorTriStateCheckboxes[0];
      var optionIdsLists =
      {
         fullyChecked    : selectorTsc.getFullyCheckedOptionIds(),
         partiallyChecked: selectorTsc.getPartiallyCheckedOptionIds()
      };

      // Make copy of array that will not be modified in loop below.
      var partiallyCheckedChildOptionIds = optionIdsLists.partiallyChecked;

      for (var i = 0; i < partiallyCheckedChildOptionIds.length; ++i)
      {
         var optionIdsListsToConcat =
         (
            _getDescendentCheckedOptionIdsRecursively(partiallyCheckedChildOptionIds[i])
         );

         optionIdsLists.fullyChecked =
         (
            optionIdsLists.fullyChecked.concat(optionIdsListsToConcat.fullyChecked)
         );

         optionIdsLists.partiallyChecked =
         (
            optionIdsLists.partiallyChecked.concat(optionIdsListsToConcat.partiallyChecked)
         );
      }

      // Note Regarding Collapsible OptionIds
      // ------------------------------------
      // The optionIds in the optionIdsLists at this point will not include optionIds for
      // collapsible options.  For example, the optionIdsLists may contain an optionId like
      // 'one|two|||three', but will not contain the collapsible parent and grandparent optionIds
      // (parent: 'one|two||', grandparent: 'one|two|').  The collapsible optionIds are therefore
      // added below.
      //    Also note that whether an optionId containing consecutive pipes ('||') is in the
      // fullyChecked list or the partiallyChecked list, the collapsible optionIds added for that
      // optionId should be added to the partiallyChecked optionIds list.  The reason is that
      // in the minimal set of optionIds to be saved at the database, the parent option of either
      // a fully checked or a partially checked options must be a partially checked option.
      optionIdsLists.partiallyChecked = optionIdsLists.partiallyChecked.concat
      (
         _getExtraOptionIdsForCollapsedColumnsFromOptionIds(optionIdsLists.fullyChecked    )
         //_getExtraOptionIdsForCollapsedColumnsFromOptionIds(optionIdsLists.partiallyChecked)
      );

      return optionIdsLists;
   };

   // Setters. --------------------------------------------------------------------------------//

   /*
    *
    */
   this.setAjaxUrl = function (newAjaxUrl)
   {
      var f = 'SelectorColumnBasedHierarchical.setAjaxUrl()';
      UTILS.checkArgs(f, arguments, ['nullOrString']);

      _state.ajaxParams.url = newAjaxUrl;

      if (_state.ajaxParams.url !== null && !_state.sentInitialisationInfoRequest)
      {
         _state.initialLoadInProgress = true;
         _state.ajaxRequestQueue.push({action: 'getInitialisationInfo', params: {}});
         _sendNextAjaxRequestInQueue();
         _state.sentInitialisationInfoRequest = true;
      }
   };

   // Other privileged functions. -------------------------------------------------------------//

   /*
    *
    */
   this.initForNewAssignmentItem = function (idAssignmentItemNew)
   {
      var f = 'SelectorColumnBasedHierarchical.initForNewAssignmentItem()';
      UTILS.checkArgs(f, arguments, ['nullOrInt']);

      idAssignmentItem                      = idAssignmentItemNew;
      _state.optionIdsToSelectAutomatically = UTILS.array.clone
      (
         _state.optionIdsToSelectAutomaticallyOriginal
      );

      var selectorTscs = _guiElements.selectorTriStateCheckboxes;

      for (var i = 0; i < selectorTscs.length; ++i)
      {
         selectorTscs[i].deleteAllOptionsAndClearState();
      }

      _state.hiddenOptionParentOptionIdsCollapsedAsKeys = {};
      _state.parentOptionIdsOfExpectedOptionSetsAsKeys  = {};
      _state.initialLoadInProgress                      = true;

      _getChildSelectorTscOptionsViaAjax(null);
      _state.sentRequestForChildSelectorTscOptionsNull = true;
   };

   /*
    *
    */
   this.setDisabledForAllInputs = function (bool)
   {
      var f = 'SelectorColumnBasedHierarchical.setDisabledForAllInputs()';
      UTILS.checkArgs(f, arguments, [Boolean]);

      var selectorTriStateCheckboxes = _guiElements.selectorTriStateCheckboxes;

      for (var i = 0; i < selectorTriStateCheckboxes.length; ++i)
      {
         selectorTriStateCheckboxes[i].setDisabledForAllInputs(bool);
      }
   };

   /*
    *
    */
   this.debugOutputCheckedOptionsToConsole = function (optionIdsLists)
   {
      var f = 'debugOutputCheckedOptionsToConsole()';
      UTILS.checkArgs(f, arguments, [Object]);

      var fChecked = optionIdsLists.fullyChecked    ;
      var pChecked = optionIdsLists.partiallyChecked;

      console.debug(f, 'fullyChecked:');
      for (var i = 0; i < fChecked.length; ++i)
      {
         console.debug(f, ' * \'' + fChecked[i] + '\'');
      }

      console.debug(f, 'partiallyChecked:');
      for (var i = 0; i < pChecked.length; ++i)
      {
         console.debug(f, ' * \'' + pChecked[i] + '\'');
      }
   };

   // Private functions. ////////////////////////////////////////////////////////////////////////

   // Event listeners. ------------------------------------------------------------------------//

   /*
    *
    */
   function _onClickSelectorTscOptionDiv(e)
   {
      try
      {
         var f = 'SelectorColumnBasedHierarchical._onClickSelectorTscOptionDiv()';
         UTILS.checkArgs(f, arguments, [Object]);

         // Note that e.target may be the span surrounding the tri-state checkbox image span.
         var optionDiv = (e.target.tagName == 'SPAN')? $(e.target).parent()[0]: e.target;

         if ($(optionDiv).hasClass('nonUserSelectable'))
         {
            throw new Exception(f, 'A non-userSelectable option triggered a click event.', '');
         }

         var o                 = _getCategoryLevelAndOptionIndexFromOptionDiv(optionDiv);
         var categoryLevel     = o.categoryLevel;
         var optionIndex       = o.optionIndex;
         var selectorTsc       = _guiElements.selectorTriStateCheckboxes[categoryLevel];
         var optionId          = selectorTsc.getOptionIdFromOptionIndex(optionIndex);
         var optionIdCollapsed = selectorTsc.getOptionIdCollapsedFromOptionIndex(optionIndex);

         if ($(optionDiv).hasClass('selected'))
         {
            if (_state.hiddenOptionParentOptionIdsCollapsedAsKeys[optionIdCollapsed] === undefined)
            {
               _getChildSelectorTscOptionsViaAjax(optionId);
            }
            else
            {
               _guiElements.selectorTriStateCheckboxes[categoryLevel + 1].revealHiddenOptions
               (
                  _getChildOptionIdsOfOptionIdCollapsed(optionIdCollapsed, true)
               );
            }

            // Only one option set per column should be visible during normal browsing.
            // Only during initial load may multiple option sets per column be displayed.
            if (!_state.initialLoadInProgress)
            {
               _unselectAllOptionsAboveLowestDisplayedCategoryLevelExcludingOption
               (
                  optionIdCollapsed
               );
            }
         }
         else
         {
            _applyCallbackToDescendentOptionsRecursively
            (
               optionIdCollapsed, _hideSelectorTscOptions, null
            );
         }
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   /*
    *
    */
   function _onClickSelectorTscOptionTriStateCheckbox(e)
   {
      try
      {
         var f = 'SelectorColumnBasedHierarchical._onClickSelectorTscOptionTriStateCheckbox()';
         UTILS.checkArgs(f, arguments, [Object]);

         var imageSpan          = e.target;
         var optionDiv          = $(imageSpan).parent().parent()[0];
         var o                  = _getCategoryLevelAndOptionIndexFromOptionDiv(optionDiv);
         var categoryLevel      = o.categoryLevel;
         var optionIndex        = o.optionIndex;
         var selectorTsc        = _guiElements.selectorTriStateCheckboxes[categoryLevel];
         var optionIdCollapsed  = selectorTsc.getOptionIdCollapsedFromOptionIndex(optionIndex);
         var optionCheckedState =
         (
            selectorTsc.getCheckedStateFromOptionIdCollapsed(optionIdCollapsed)
         );

         if (categoryLevel < _state.nCategoryLevels - 1)
         {
            _applyCallbackToDescendentOptionsRecursively
            (
               optionIdCollapsed, _setSelectorTscOptionCheckedStates, optionCheckedState
            );
         }

         if (categoryLevel > 0)
         {
            _applyCallbackToAncestorOptionsRecursively
            (
               optionIdCollapsed, _setSelectorTscOptionCheckedStateDependingOnChildren, null
            );
         }
      }
      catch (e)
      {
         UTILS.printExceptionToConsole(f, e);
      }
   }

   // Getters. --------------------------------------------------------------------------------//

   /*
    * NOTE: This function should be supplied with an optionId, not an optionIdCollapsed.
    */
   function _getChildSelectorTscOptionsViaAjax(optionId)
   {
      var f = 'SelectorColumnBasedHierarchical._getChildSelectorTscOptionsViaAjax()';
      UTILS.checkArgs(f, arguments, ['nullOrString']);

      _state.ajaxRequestQueue.push
      (
         {
            action: 'getChildSelectorTriStateCheckboxOptions',
            params: {idAssignmentItem: idAssignmentItem, optionId: optionId}
         }
      );

      _sendNextAjaxRequestInQueue();
   }

   // Initialisation functions. ---------------------------------------------------------------//

   /*
    *
    */
   function _init()
   {
      var f = 'SelectorColumnBasedHierarchical._init()';
      UTILS.checkArgs(f, arguments, []);

      var mainTable         = _domElements.tables.main;
      var mainTableChildren = $(mainTable).children();
      var thead             = mainTableChildren[0];
      var tbody             = mainTableChildren[1];

      // Add trs to main table.
      $(thead).append(_domElements.trs.headingsTr);
      $(tbody).append(_domElements.trs.optionsTr );

      if (_state.ajaxParams.url !== null && !_state.sentInitialisationInfoRequest)
      {
         _state.initialLoadInProgress = true;
         _state.ajaxRequestQueue.push({action: 'getInitialisationInfo', params: {}});
         _sendNextAjaxRequestInQueue();
         _state.sentInitialisationInfoRequest = true;
      }
   }

   /*
    *
    */
   function _initStateUsingInitialisationInfoFromServer(initInfo)
   {
      var f = 'SelectorColumnBasedHierarchical._initStateUsingInitialisationInfoFromServer()';
      UTILS.checkArgs(f, arguments, [Object]);

      UTILS.validator.checkObject
      (
         initInfo,
         {
            lowestCategoryLevelToDisplay  : 'nonNegativeInt',
            highestCategoryLevelToDisplay : 'nonNegativeInt',
            optionIdsToSelectAutomatically: 'array'         ,
            categoryLevelNames            : 'array'
         }
      );

      if (initInfo.lowestCategoryLevelToDisplay > initInfo.highestCategoryLevelToDisplay)
      {
         throw new Exception
         (
            'Lowest category level to display exceeds highest category level to display.'
         );
      }

      _state.lowestCategoryLevelToDisplay           = initInfo.lowestCategoryLevelToDisplay;
      _state.highestCategoryLevelToDisplay          = initInfo.highestCategoryLevelToDisplay;
      _state.optionIdsToSelectAutomaticallyOriginal = initInfo.optionIdsToSelectAutomatically;
      _state.optionIdsToSelectAutomatically         = UTILS.array.clone
      (
         _state.optionIdsToSelectAutomaticallyOriginal
      );

      _initTableRows(initInfo.categoryLevelNames);
   }

   /*
    *
    */
   function _initTableRows(categoryLevelNames)
   {
      var f = 'SelectorColumnBasedHierarchical._initTableRows()';
      UTILS.checkArgs(f, arguments, [Array]);

      var nColumns       = categoryLevelNames.length;
      var headingsTr      = _domElements.trs.headingsTr;
      var optionsTr       = _domElements.trs.optionsTr;
      var percentageWidth = Math.floor(100 / nColumns);

      $(headingsTr).empty();
      $(optionsTr ).empty();

      for (var i = 0; i < nColumns; ++i)
      {
         var div = DIV();
         $(div).css('float', 'left');

         var selectorTsc = new SelectorTriStateCheckbox(div, []);

         $(headingsTr).append(TH({style: 'width: ' + percentageWidth + '%'},categoryLevelNames[i]));
         $(optionsTr ).append(TD(selectorTsc.getDiv()));
         _guiElements.selectorTriStateCheckboxes.push(selectorTsc);
      }

      _state.categoryLevelNames = categoryLevelNames;
      _state.nCategoryLevels   = categoryLevelNames.length;

      // Do not display columns at categories that are not accessible.
      var headingTds = $(headingsTr).children();
      var optionTds  = $(optionsTr ).children();
      for (var i = 0; i < _state.nCategoryLevels; ++i)
      {
         if (i < _state.lowestCategoryLevelToDisplay || i > _state.highestCategoryLevelToDisplay)
         {
            $(headingTds[i]).css('display', 'none');
            $(optionTds[i]).css('display', 'none');
         }
      }
   }

   // Other private functions. /////////////////////////////////////////////////////////////////

   /*
    *
    */
   function _addSelectorTriStateCheckboxOptions(info)
   {
      var f = 'SelectorColumnBasedHierarchical._addSelectorTriStateCheckboxOptions()';
      UTILS.checkArgs(f, arguments, [Object]);

      UTILS.validator.checkObject
      (
         info,
         {
            categoryLevel   : 'int'        ,
            idAssignmentItem: 'positiveInt',
            optionsInfo     : 'array'      ,
            parentOptionId  : 'nullOrString'
         }
      );

      if (info.idAssignmentItem != idAssignmentItem)
      {
         // The idAssignmentItem has changed since the info passed to this function
         // was requested from the server.  The info should be ignored in this case.
         return;
      }

      _trackOptionsRequestedAndReceived(info.parentOptionId, 'received');

      var categoryLevel = info.categoryLevel;
      var optionsInfo   = info.optionsInfo;
      var selectorTscs  = _guiElements.selectorTriStateCheckboxes;
      var selectorTsc   = selectorTscs[categoryLevel];

      // NOTE: The optionsInfo array may be empty if the user has limited privileges.

      if (boolViewOnlyMode)
      {
         // Override 'checkable' property of all options.
         for (var i = 0; i < optionsInfo.length; ++i)
         {
            optionsInfo[i].userCheckable = false;
         }
      }

      // If the options have a parent option...
      if (categoryLevel > 0)
      {
         // ASSUMPTION: All options in optionsInfo have the same parent option.
         var optionIdCollapsed        = optionsInfo[0].optionIdCollapsed;
         var parentCategoryLevel      = categoryLevel - 1;
         var parentSelectorTsc        =_guiElements.selectorTriStateCheckboxes[parentCategoryLevel];
         var parentOptionIdCollapsed  = _getParentOptionIdFromOptionId(optionIdCollapsed);
         var parentOptionCheckedState =
         (
            parentSelectorTsc.getCheckedStateFromOptionIdCollapsed(parentOptionIdCollapsed)
         );

         // If the parent option is unchecked, then the child options must be displayed as
         // unchecked even though the database at the server may tell us the options are checked.
         // This situation will arise if the user has unchecked the parent option without saving
         // his/her changes.
         if (parentOptionCheckedState == 'unchecked')
         {
            for (var i = 0; i < optionsInfo.length; ++i)
            {
               optionsInfo[i].checkedState = 'unchecked';
            }
         }
      }

      selectorTsc.addOptions(optionsInfo);
      selectorTsc.updateTriStateCheckboxHeights();

      var optionDivs                     = $(selectorTsc.getDiv()).find('div');
      var optionIdsToSelectAutomatically = _state.optionIdsToSelectAutomatically;

      // For each option div added...
      for (var i = optionDivs.length - optionsInfo.length; i < optionDivs.length; ++i)
      {
         var optionDiv         = optionDivs[i];
         var optionId          = selectorTsc.getOptionIdFromOptionIndex(i);
         var optionIdCollapsed = selectorTsc.getOptionIdCollapsedFromOptionIndex(i);

         if (!$(optionDiv).hasClass('nonUserSelectable'))
         {
            $(optionDiv).click(_onClickSelectorTscOptionDiv);
         }

         if (!$(optionDiv).hasClass('nonUserCheckable'))
         {
            $(optionDiv).find('span.triStateCheckboxImage').click
            (
               _onClickSelectorTscOptionTriStateCheckbox
            );

            if (parentOptionCheckedState == 'checked')
            {
               selectorTsc.setCheckedStateForOptionAtIndex(i, 'checked');
            }
         }

         if
         (
            selectorTsc.getCheckedStateFromOptionIdCollapsed(optionIdCollapsed) ==
            (
               'partiallyChecked'
            )
         )
         {
            // Simulate a click on the option div so as to
            // cause its child options to be requested via AJAX.
            $(optionDiv).click();
         }
      }

      if (optionIdsToSelectAutomatically.length > 0)
      {
         // Simulate a click on the first option to automatically request.
         var optionId          = optionIdsToSelectAutomatically.pop();
         var optionIdCollapsed = _collapseOptionId(optionId);
         var categoryLevel     = _getCategoryLevelFromOptionIdCollapsedOrNot(optionIdCollapsed);
         var selectorTsc       = _guiElements.selectorTriStateCheckboxes[categoryLevel];
         var selectorTscDiv    = selectorTsc.getDiv();
         var optionIndex       = selectorTsc.getOptionIndexFromOptionIdCollapsed(optionIdCollapsed);
         var optionDiv         = $(selectorTscDiv).children()[optionIndex];

         if (!$(optionDiv).hasClass('selected'))
         {
            $(optionDiv).click();
         }
      }
   }

   /*
    *
    */
   function _trackOptionsRequestedAndReceived(parentOptionId, requestedORreceived)
   {
      var f = 'SelectorColumnBasedHierarchical._trackOptionsRequestedAndReceived()';
      UTILS.checkArgs(f, arguments, ['nullOrString', String]);

      if (parentOptionId === null)
      {
         // Convert to string so can be stored as object key.
         parentOptionId = 'NULL';
      }

      switch (requestedORreceived)
      {
       case 'requested':
         if (_state.parentOptionIdsOfExpectedOptionSetsAsKeys[parentOptionId] !== undefined)
         {
            throw new Exception
            (
               f, "A request for child options of option '" + parentOptionId +
               "' has already been logged.", ''
            );
         }
         _state.parentOptionIdsOfExpectedOptionSetsAsKeys[parentOptionId] = null;
         break;

       case 'received':
         delete _state.parentOptionIdsOfExpectedOptionSetsAsKeys[parentOptionId];
         break;

       default:
         throw new Exception
         (
            f, "Expected 'requested' or 'received'.  Received '" + requestedORreceived + "'.", ''
         );
      }
   }

   /*
    *
    */
   function _applyCallbackToDescendentOptionsRecursively(optionIdCollapsed, callback, param)
   {
      var f = 'SelectorColumnBasedHierarchical._applyCallbackToDescendentOptionsRecursively()';
      UTILS.checkArgs(f, arguments, [String, 'Defined', 'Defined']);

      var childCategoryLevel = _getCategoryLevelFromOptionIdCollapsedOrNot(optionIdCollapsed) + 1;

      if (childCategoryLevel != _state.nCategoryLevels)
      {
         var childSelectorTsc = _guiElements.selectorTriStateCheckboxes[childCategoryLevel];
         var idsOfChildOptionsCollapsed =
         (
            _getChildOptionIdsOfOptionIdCollapsed(optionIdCollapsed, true)
         );

         callback(childSelectorTsc, idsOfChildOptionsCollapsed, param);

         for (var i = 0; i < idsOfChildOptionsCollapsed.length; ++i)
         {
            _applyCallbackToDescendentOptionsRecursively
            (
               idsOfChildOptionsCollapsed[i], callback, param
            );
         }
      }
   }

   /*
    *
    */
   function _applyCallbackToAncestorOptionsRecursively(optionIdCollapsed, callback, param)
   {
      var f = 'SelectorColumnBasedHierarchical._applyCallbackToAncestorOptionsRecursively()';
      UTILS.checkArgs(f, arguments, [String, 'Defined', 'Defined']);

      var parentCategoryLevel = _getCategoryLevelFromOptionIdCollapsedOrNot(optionIdCollapsed) - 1;
      var parentSelectorTsc       = _guiElements.selectorTriStateCheckboxes[parentCategoryLevel];
      var parentOptionIdCollapsed = _getParentOptionIdFromOptionId(optionIdCollapsed);

      callback(parentSelectorTsc, parentOptionIdCollapsed, param);

      // If the parent category level is not the lowest level...
      if (parentCategoryLevel != 0)
      {
         _applyCallbackToAncestorOptionsRecursively(parentOptionIdCollapsed, callback, param);
      }
   }

   /*
    *
    */
   function _getChildOptionIdsOfOptionIdCollapsed(optionIdCollapsed, boolCollapseReturnedOptionIds)
   {
      var f = 'SelectorColumnBasedHierarchical._getChildOptionIdsOfOptionIdCollapsed()';
      UTILS.checkArgs(f, arguments, [String, Boolean]);

      var optionCategoryLevel = _getCategoryLevelFromOptionIdCollapsedOrNot(optionIdCollapsed);

      if (optionCategoryLevel < 0 || childCategoryLevel >= _state.nCategoryLevels)
      {
         throw new Exception
         (
            f, 'Option category level (' + optionCategoryLevel + ') out of range.', ''
         );
      }

      if (optionCategoryLevel == _state.nCategoryLevels - 1)
      {
         return [];
      }

      var childCategoryLevel = optionCategoryLevel + 1;
      var childSelectorTsc   = _guiElements.selectorTriStateCheckboxes[childCategoryLevel];
      var childOptionDivs    = $(childSelectorTsc.getDiv()).find('div');
      var childOptionIds     = childSelectorTsc.getOptionIds();
      var idsOfChildOptions  = [];

      for (var i = 0; i < childOptionIds.length; ++i)
      {
         var childOptionId           = childOptionIds[i];
         var childOptionIdCollapsed  = _collapseOptionId(childOptionId);
         var parentOptionIdCollapsed = _getParentOptionIdFromOptionId(childOptionIdCollapsed);

         if (optionIdCollapsed == parentOptionIdCollapsed)
         {
            idsOfChildOptions.push
            (
               (boolCollapseReturnedOptionIds)? childOptionIdCollapsed: childOptionId
            );
         }
      }

      return idsOfChildOptions;
   }

   /*
    * IMPORTANT
    * ---------
    * This function can be used on either collapsed or non-collapsed optionIds, but the meaning of
    * the returned category level will depend on whether the supplied optionId was collapsed or
    * not.  If the supplied optionId is non-collapsed, then the category level returned will be the
    * true category level for that option (as recorded at the server).  If the supplied optionId is
    * collapsed, then the category level returned will be the category level at which the option is
    * displayed by the client.  The programmer must understand the distinction.
    */
   function _getCategoryLevelFromOptionIdCollapsedOrNot(optionId)
   {
      var f = 'SelectorTsc._getCategoryLevelFromOptionIdCollapsedOrNot()';
      UTILS.checkArgs(f, arguments, [String]);

      var tokens         = optionId.split('|');
      var nTokensCounted = 0;

      for (var i = 0; i < tokens.length; ++i)
      {
         ++nTokensCounted;
      }

      // The first token should never be an empty
      // string, and so should always be counted.
      UTILS.assert(f, 0, nTokensCounted >= 1);

      return nTokensCounted - 1;
   }

   /*
    *
    */
   function _collapseOptionId(optionId)
   {
      var f = 'SelectorColumnBasedHierarchical._collapseOptionId()';
      UTILS.checkArgs(f, arguments, [String]);

      var tokens    = optionId.split('|');
      var newTokens = [];

      for (var i = 0; i < tokens.length; ++i)
      {
         var token = tokens[i];

         if (token != '')
         {
            newTokens.push(token);
         }
      }

      return newTokens.join('|');
   }

   /*
    * NOTE: This function is intended for use on both collapsed and non-collapsed optionIds.
    */
   function _getParentOptionIdFromOptionId(optionIdCollapsedOrNot)
   {
      var f = 'SelectorColumnBasedHierarchical._getParentOptionIdFromOptionId()';
      UTILS.checkArgs(f, arguments, [String]);

      // Note on Option ID Format
      // ------------------------
      // Ancestor category ids are stored in the optionId of each option in the following format.
      //  * '<parentOptionIdCollapsed>|optionId>'
      //
      // Note that optionIdsCollapsed by definition never contain consecutive pipes ('||').
      // If an option id were to contain consecutive pipes, it would not be collapsed.
      //
      // Example for Collapsed Option ID
      // -------------------------------
      // An option having optionId 'one|five|eight' has a parent option with optionId 'one|five',
      // and a grandparent option having optionId 'one'.  The grandparent option has no parent.
      // Therefore the grandparent option has category level 0, the parent option has category
      // level 1, and the original option has category level 2.
      //
      // Example for Non-Collapsed Option ID
      // -----------------------------------
      // An option having optionId 'one|five||eight' has a parent option with optionId 'one|five|',
      // and a grandparent option having optionId 'one|five'.  The great-grandparent option has an
      // optionId 'one'.  The great-grandparent option has no parent.  Therefore the great-
      // grandparent option has category level 0, the grandparent option has category level 1, the
      // parent option has category level 2, and the original option has category level 3.
      //
      // Option ids must be set to match this format at the server.

      var lastPipeIndex = optionIdCollapsedOrNot.lastIndexOf('|');

      return optionIdCollapsedOrNot.substr(0, lastPipeIndex);
   }

   /*
    *
    */
   function _getCategoryLevelAndOptionIndexFromOptionDiv(optionDiv)
   {
      var f = 'SelectorColumnBasedHierarchical._getCategoryLevelAndOptionIndexFromOptionDiv()';
      UTILS.checkArgs(f, arguments, [HTMLDivElement]);

      var td = $(optionDiv).parent().parent();

      return o =
      {
         categoryLevel: UTILS.DOM.countPreviousSiblings(td       ),
         optionIndex  : UTILS.DOM.countPreviousSiblings(optionDiv)
      }
   }

   /*
    *
    */
   function _hideSelectorTscOptions(selectorTsc, optionIdsCollapsed, dummyParam)
   {
      var f = 'SelectorColumnBasedHierarchy._hideSelectorTscOptions()';
      UTILS.checkArgs(f, arguments, [SelectorTriStateCheckbox, Array, 'Defined']);

      if (optionIdsCollapsed.length > 0)
      {
         // ASSUMPTION
         // ----------
         // All optionIdsCollapsed passed to this function have the same parent optionId.
         // This should be true for optionIdsCollapsed, but not for optionIds.
         var parentOptionIdCollapsed = _getParentOptionIdFromOptionId(optionIdsCollapsed[0]);

         _state.hiddenOptionParentOptionIdsCollapsedAsKeys[parentOptionIdCollapsed] = null;

         selectorTsc.hideOptions(optionIdsCollapsed);
         selectorTsc.updateTriStateCheckboxHeights();
      }
   }

   /*
    *
    */
   function _setSelectorTscOptionCheckedStates(selectorTsc, optionIdsCollapsed, newState)
   {
      var f = 'SelectorColumnBasedHierarchy._setSelectorTscOptionCheckedStates()';
      UTILS.checkArgs(f, arguments, [SelectorTriStateCheckbox, Array, String]);

      selectorTsc.setCheckedStates(optionIdsCollapsed, newState);
   }

   /*
    *
    */
   function _setSelectorTscOptionCheckedStateDependingOnChildren
   (
      selectorTsc, optionIdCollapsed, dummyParam
   )
   {
      var f = 'SelectorColumnBasedHierarchy._setSelectorTscOptionCheckedStateDependingOnChildren()';
      UTILS.checkArgs(f, arguments, [SelectorTriStateCheckbox, String, 'Defined']);

      var childOptionIdsCollapsed = _getChildOptionIdsOfOptionIdCollapsed(optionIdCollapsed, true);

      if (childOptionIdsCollapsed.length == 0)
      {
         return;
      }

      var childCategoryLevel   = _getCategoryLevelFromOptionIdCollapsedOrNot(optionIdCollapsed) + 1;
      var childSelectorTsc     = _guiElements.selectorTriStateCheckboxes[childCategoryLevel];
      var countsByCheckedState = {checked: 0, unchecked: 0, partiallyChecked: 0};
      var nNonHeadingOptions   = 0;

      for (var i = 0; i < childOptionIdsCollapsed.length; ++i)
      {
         var childOptionIdCollapsed = childOptionIdsCollapsed[i];

         if (!childSelectorTsc.optionIsHeading(childOptionIdCollapsed))
         {
            ++nNonHeadingOptions;
            ++countsByCheckedState[
               childSelectorTsc.getCheckedStateFromOptionIdCollapsed(childOptionIdCollapsed)
            ];
         }
      }

      var newCheckedState =
      (
         (countsByCheckedState.checked == nNonHeadingOptions)? 'checked':
         (
            (countsByCheckedState.unchecked == nNonHeadingOptions)? 'unchecked': 'partiallyChecked'
         )
      );

      selectorTsc.setCheckedStates([optionIdCollapsed], newCheckedState);
   }

   /*
    *
    */
   function _unselectAllOptionsAboveLowestDisplayedCategoryLevelExcludingOption(optionIdCollapsed)
   {
      var f =
      (
         'SelectorColumnBasedHierarchical.' +
         '_unselectAllOptionsAboveLowestDisplayedCategoryLevelExcludingOption()'
      );
      UTILS.checkArgs(f, arguments, [String]);

      var ancestorsPlusSelfOptionIdsCollapsedByCategoryLevel = [];
      var categoryLevel = _getCategoryLevelFromOptionIdCollapsedOrNot(optionIdCollapsed);
      var lowestCategoryLevelToDisplay = _state.lowestCategoryLevelToDisplay;
      var selectorTscsByCategoryLevel  = _guiElements.selectorTriStateCheckboxes;

      // Get list of ancestor plus self options to leave selected
      // (only from category levels above the lowest displayed level).
      ancestorsPlusSelfOptionIdsCollapsedByCategoryLevel[categoryLevel] = optionIdCollapsed;
      for (var c = categoryLevel - 1; c >= lowestCategoryLevelToDisplay; --c)
      {
         var parentOptionIdCollapsed = _getParentOptionIdFromOptionId(optionIdCollapsed);
         ancestorsPlusSelfOptionIdsCollapsedByCategoryLevel[c] = parentOptionIdCollapsed;
         var optionIdCollapsed = parentOptionIdCollapsed;
      }

      // Unselect all selected options that are not in the list collated above.
      for (var c = lowestCategoryLevelToDisplay; c <= categoryLevel; ++c)
      {
         var ancestorOptionIdCollapsed = ancestorsPlusSelfOptionIdsCollapsedByCategoryLevel[c];
         var selectorTsc               = selectorTscsByCategoryLevel[c];
         var optionIdsCollapsed        = selectorTsc.getSelectedOptionIdsCollapsed();

         for (var i = 0; i < optionIdsCollapsed.length; ++i)
         {
            var optionIdCollapsed = optionIdsCollapsed[i];
            var optionDivs        = $(selectorTsc.getDiv()).find('div');

            if (optionIdCollapsed != ancestorOptionIdCollapsed)
            {
               var optionIndex = selectorTsc.getOptionIndexFromOptionIdCollapsed(optionIdCollapsed);
               $(optionDivs[optionIndex]).click();
            }
         }
      }
   }

   // Private functions dealing with non-collapsed option ids. --------------------------------//

   /*
    * Return a minimal set of checked option ids, that allows the full set of checked option ids to
    * be determined given the tree hierarchy structure of the selector.  For example, if all
    * child options of a given option are checked, then the parent option is by definition checked,
    * and only the parent optionId need be returned.
    *
    * IMPORTANT
    * ---------
    * Note also that this function deals with optionIds, not optionIdsCollapsed.  The reason is
    * that the fully and partially checked optionIds are to be sent to the server.  The collapsed
    * optionIds exist only for the client.  Collapsed optionIds however must be used by this
    * function in order to determine which category level the given option is displayed at by the
    * client.  This information allows the correct SelectorTriStateCheckbox to be aquired.
    *
    * Algorithm Description
    * ---------------------
    * STEP 1:
    *    Collate two lists of ids of child options:
    *     * ids of fully     checked child options,
    *     * ids of partially checked child options.
    * STEP 2:
    *    Recursively find all ids of descendent partially checked options and a minimal set of
    *    descendent fully checked options (not including descendents of fully checked options)
    *    by calling this function only on each partially checked option.
    */
   function _getDescendentCheckedOptionIdsRecursively(optionId)
   {
      var f = 'SelectorColumnBasedHierarchical._getDescendentCheckedOptionIdsRecursively()';
      UTILS.checkArgs(f, arguments, [String]);

      var optionIdCollapsed  = _collapseOptionId(optionId);
      var childCategoryLevel = _getCategoryLevelFromOptionIdCollapsedOrNot(optionIdCollapsed) + 1;
      var childSelectorTsc   = _guiElements.selectorTriStateCheckboxes[childCategoryLevel];
      var optionIdsLists     = {fullyChecked: [], partiallyChecked: []};
      var idsOfChildOptions  = _getChildOptionIdsOfOptionIdCollapsed(optionIdCollapsed, false);

      // STEP 1 (see Algorithm description above).
      for (var i = 0; i < idsOfChildOptions.length; ++i)
      {
         var childOptionId = idsOfChildOptions[i];

         switch (childSelectorTsc.getCheckedStateFromOptionId(childOptionId))
         {
          case 'checked'         : optionIdsLists.fullyChecked.push(childOptionId)    ; break;
          case 'partiallyChecked': optionIdsLists.partiallyChecked.push(childOptionId); break;
          default: // Do nothing.
         }
      }

      // If the child category level is the highest level...
      if (childCategoryLevel == _state.nCategoryLevels - 1)
      {
         if (optionIdsLists.partiallyChecked.length != 0)
         {
            throw new Exception(f, 'Partially checked option found in highest category level.', '');
         }
      }

      // Make a copy of array that will not be modified in the loop below.
      var partiallyCheckedChildOptionIds = optionIdsLists.partiallyChecked;

      // STEP 2 (see Algorithm description above).
      for (var i = 0; i < partiallyCheckedChildOptionIds.length; ++i)
      {
         var optionIdsListsToConcat =
         (
            _getDescendentCheckedOptionIdsRecursively(partiallyCheckedChildOptionIds[i])
         );

         optionIdsLists.fullyChecked =
         (
            optionIdsLists.fullyChecked.concat(optionIdsListsToConcat.fullyChecked)
         );

         optionIdsLists.partiallyChecked =
         (
            optionIdsLists.partiallyChecked.concat(optionIdsListsToConcat.partiallyChecked)
         );
      }

      return optionIdsLists;
   }

   /*
    * Return for each optionId found containing consecutive pipes ('||'),
    * a new optionId for each collapsible option referred to in that optionId.
    *
    * Example:
    *    If optionIds =
    *       ['one|two|three', 'four|five||six', 'seven||eight|||nine'],
    *
    *    the returned array should be:
    *       ['four|five|', 'seven|', 'seven||eight|', 'seven||eight||'].
    */
   function _getExtraOptionIdsForCollapsedColumnsFromOptionIds(optionIds)
   {
      var f ='SelectorColumnBasedHierarchical._getExtraOptionIdsForCollapsedColumnsFromOptionIds()';
      UTILS.checkArgs(f, arguments, [Array]);

      var extraOptionIds = [];

      for (var i = 0; i < optionIds.length; ++i)
      {
         var optionId         = optionIds[i];
         var searchStartIndex = 0;
         var pos              = null;

         while ((pos = optionId.indexOf('||', searchStartIndex)) != -1)
         {
            extraOptionIds.push(optionId.substring(0, pos + 1));
            searchStartIndex = pos + 1;
         }
      }

      return extraOptionIds;
   }

   /*
    * Use this function rather than calling $.ajax(_state.ajaxParams) directly.
    * This function uses a queue to ensure that no new message is sent until a reply to the
    * previous message sent has been received.
    */
   function _sendNextAjaxRequestInQueue()
   {
      var f = 'SelectorColumnBasedHierarchical._sendNextAjaxRequestInQueue()';
      UTILS.checkArgs(f, arguments, []);

      if (_state.ajaxParams.url === null)
      {
         throw new Exception(f, 'Attemped to send AJAX message when ajax URL not set.', '');
      }

      if (_state.ajaxRequestQueue.length > 0 && !_state.waitingForAjaxReply)
      {
         var ajaxDataUnencoded = _state.ajaxRequestQueue.shift();

         if (ajaxDataUnencoded.action == 'getChildSelectorTriStateCheckboxOptions') {
            _trackOptionsRequestedAndReceived(ajaxDataUnencoded.params.optionId, 'requested');
         }

         _state.ajaxParams.data = JSON.stringify(ajaxDataUnencoded);
         $.ajax(_state.ajaxParams);
         _state.waitingForAjaxReply = true;
      }
   }

   /*
    *
    */
   function _ajaxReplyPostProcessCommon()
   {
      var f = 'SelectorColumnBasedHierarchical._ajaxReplyPostProcessCommon()';
      UTILS.checkArgs(f, arguments, []);

      _state.waitingForAjaxReply = false;
      _sendNextAjaxRequestInQueue();

      if (!_state.waitingForAjaxReply)
      {
         _state.initialLoadInProgress = false;
         onFinishAutoLoadingOptions();
      }
   }

   // Private variables. ////////////////////////////////////////////////////////////////////////

   var self = this;

   var _domElements =
   {
      tables:
      {
         main: TABLE
         (
            {'class': 'selectorColumnBasedHierarchical', style: 'width: 100%'},
            THEAD(),
            TBODY()
         )
      },
      trs:
      {
         headingsTr: TR(),
         optionsTr : TR()
      }
   };

   var _guiElements =
   {
      selectorTriStateCheckboxes: []
   };

   var _state =
   {
      categoryLevelNames                        : null ,
      hiddenOptionParentOptionIdsCollapsedAsKeys: {}   ,
      highestCategoryLevelToDisplay             : null ,
      initialLoadInProgress                     : null ,
      lowestCategoryLevelToDisplay              : null ,
      nCategoryLevels                           : null ,
      optionIdsToSelectAutomatically            : []   ,
      optionIdsToSelectAutomaticallyOriginal    : []   ,
      parentOptionIdsOfExpectedOptionSetsAsKeys : {}   ,
      sentInitialisationInfoRequest             : false,
      sentRequestForChildSelectorTscOptionsNull : false,
      ajaxRequestQueue                          : []   ,
      waitingForAjaxReply                       : false,
      ajaxParams                                :
      {
         dataType: 'json' ,
         type    : 'POST' ,
         url     : ajaxUrl,
         success : UTILS.ajax.createReceiveAjaxMessageFunction
         (
            'SelectorColumnBasedHierarchical', displayAjaxFailureMessageFunction,
            {
               getInitialisationInfo: function (reply)
               {
                  var f = 'SelectorColumnBasedHierarchical.ajaxResponder1()';
                  UTILS.checkArgs(f, arguments, [Object]);
                  _initStateUsingInitialisationInfoFromServer(reply);

                  if (idAssignmentItem !== null &&!_state.sentRequestForChildSelectorTscOptionsNull)
                  {
                     _getChildSelectorTscOptionsViaAjax(null);
                     _state.sentRequestForChildSelectorTscOptionsNull = true;
                  }

                  _ajaxReplyPostProcessCommon();
               },

               getChildSelectorTriStateCheckboxOptions: function (reply)
               {
                  var f = 'SelectorColumnBasedHierarchical.ajaxResponder2()';
                  UTILS.checkArgs(f, arguments, [Object]);
                  _addSelectorTriStateCheckboxOptions(reply);
                  _ajaxReplyPostProcessCommon();
               }
            }
         )
      }
   };

   // Initialisation code. //////////////////////////////////////////////////////////////////////

   _init();
}

/*******************************************END*OF*FILE********************************************/
