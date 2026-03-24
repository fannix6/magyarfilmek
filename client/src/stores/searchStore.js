import { defineStore } from "pinia";

export const useSearchStore = defineStore("search", {
  //Ezek a változók
  state: () => ({
    searchWord: '',
    searchMode: 'title',
  }),
  //valamilyen formában visszaadja
  getters: {
    searchword() {
      return this.searchWord.toLowerCase();
    },
  },
  //csinál vele valamit
  actions: {
    resetSearchWord(){
        this.searchWord = '';
    },
    setSearchWord(value){
        this.searchWord = value.trim();
    },
    setSearchMode(mode){
        this.searchMode = mode === 'year' ? 'year' : 'title';
    }
  },
});
