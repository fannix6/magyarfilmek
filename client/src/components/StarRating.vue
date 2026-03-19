<template>
  <div
    class="star-rating"
    :class="[`star-rating--${size}`, { 'star-rating--readonly': readonly }]"
    role="radiogroup"
    aria-label="Star rating"
    @mouseleave="clearHover"
  >
    <button
      v-for="n in max"
      :key="n"
      type="button"
      class="star-rating__item"
      :disabled="readonly"
      :aria-label="`Set rating to ${n} stars`"
      @mousemove="previewValue($event, n)"
      @click="setValue($event, n)"
    >
      <i :class="starIcon(n)"></i>
    </button>
  </div>
</template>

<script>
export default {
  name: "StarRating",
  props: {
    modelValue: {
      type: Number,
      default: 0,
    },
    max: {
      type: Number,
      default: 5,
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    size: {
      type: String,
      default: "md",
    },
  },
  data() {
    return {
      hoverValue: null,
    };
  },
  computed: {
    currentValue() {
      return this.hoverValue === null ? this.modelValue : this.hoverValue;
    },
  },
  methods: {
    valueFromEvent(event, starIndex) {
      const rect = event.currentTarget.getBoundingClientRect();
      const offset = event.clientX - rect.left;
      const isHalf = offset < rect.width / 2;
      return starIndex - (isHalf ? 0.5 : 0);
    },
    previewValue(event, starIndex) {
      if (this.readonly) return;
      this.hoverValue = this.valueFromEvent(event, starIndex);
    },
    setValue(event, starIndex) {
      if (this.readonly) return;
      const nextValue = this.valueFromEvent(event, starIndex);
      this.$emit("update:modelValue", nextValue);
    },
    clearHover() {
      this.hoverValue = null;
    },
    starIcon(starIndex) {
      if (this.currentValue >= starIndex) return "bi bi-star-fill";
      if (this.currentValue >= starIndex - 0.5) return "bi bi-star-half";
      return "bi bi-star";
    },
  },
};
</script>

<style scoped>
.star-rating {
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
}

.star-rating__item {
  border: 0;
  background: transparent;
  color: #e39ef8;
  padding: 0;
  line-height: 1;
  cursor: pointer;
}

.star-rating__item i {
  font-size: 1.1rem;
}

.star-rating--lg .star-rating__item i {
  font-size: 1.6rem;
}

.star-rating--readonly .star-rating__item {
  cursor: default;
}

.star-rating--readonly .star-rating__item:disabled {
  opacity: 1;
}
</style>
