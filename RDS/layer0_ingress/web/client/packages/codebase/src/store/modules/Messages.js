const getDefaultState = () => {
  return {
    messages: {},
    icons: {
      success: "mdi-check-circle",
      error: "mdi-alert-decagram",
      warning: "mdi-help-circle",
    },
  };
};

export default {
  getDefaultState,
  name: "MessagesStore",
  state: getDefaultState(),

  getters: {
    getMessagesByResearchIndex: (state) => (id) => state.messages[id] || null,
    getIconByType: (state) => (type) => state.icons[type] || null,
  },
  mutations: {
    addMessage: (state, payload) =>
      (state.messages[payload.researchIndex] = [
        ...(state.messages[payload.researchIndex] || []),
        payload,
      ]),
  },
  actions: {
    SOCKET_fileUploadStatus(context, payload) {
      context.commit("addMessage", payload);
    },
  },
};
