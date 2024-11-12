const upcoming_date_query_loop_name = "upcoming-date-query-loop"
const past_date_query_loop_name = "past-date-query-loop"
const organisations_we_support_query_loop_name =
   "organisations-we-support-query-loop"

wp.blocks.registerBlockVariation("core/query", {
   name: upcoming_date_query_loop_name,
   title: "Upcoming Date Query Loop",
   description: "Returns events whose end date is on or after today's date.",
   isActive: ({ namespace, query }) => {
      return (
         namespace === upcoming_date_query_loop_name &&
         query.postType === "event"
      )
   },
   icon: "calendar-alt",
   attributes: {
      namespace: upcoming_date_query_loop_name,
      query: {
         postType: "event",
         offset: 0,
      },
   },

   scope: ["inserter"],
   allowedControls: ["perPage", "taxQuery"],
   innerBlocks: [["core/post-template", {}, []]],
})

wp.blocks.registerBlockVariation("core/query", {
   name: past_date_query_loop_name,
   title: "Past Date Query Loop",
   description: "Returns events whose end date is before today's date.",
   isActive: ({ namespace, query }) => {
      return (
         namespace === past_date_query_loop_name && query.postType === "event"
      )
   },
   icon: "calendar-alt",
   attributes: {
      namespace: past_date_query_loop_name,
      query: {
         postType: "event",
         offset: 0,
      },
   },

   scope: ["inserter"],
   allowedControls: ["perPage", "taxQuery"],
   innerBlocks: [["core/post-template", {}, []]],
})

wp.blocks.registerBlockVariation("core/query", {
   name: organisations_we_support_query_loop_name,
   title: "Organisations We Support Query Loop",
   description: "Returns organisations.",
   isActive: ({ namespace, query }) => {
      return (
         namespace === organisations_we_support_query_loop_name &&
         query.postType === "organisation"
      )
   },
   icon: "groups",
   attributes: {
      namespace: organisations_we_support_query_loop_name,
      query: {
         postType: "organisation",
         offset: 0,
      },
   },

   scope: ["inserter"],
   allowedControls: ["perPage", "taxQuery"],
   innerBlocks: [["core/post-template", {}, []]],
})
